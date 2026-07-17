<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\AggregateCommand;
use App\Command\AggregateConfig;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

/**
 * Covers the environment/config-parsing layer of the aggregate command. These are
 * the knobs that decide data retention, per-server slow/heavy thresholds, and who
 * gets alerted — a silent parsing bug here misconfigures monitoring without any
 * error surfacing, so the exact contracts are pinned here.
 *
 * The parsing methods are private but pure (no DB/mail side effects), so they are
 * exercised directly with reflection over an instance built from mocked collaborators.
 */
final class AggregateCommandConfigTest extends TestCase
{
    /** @var list<string> env keys set during a test, cleared in tearDown */
    private array $touchedEnv = [];

    protected function tearDown(): void
    {
        foreach ($this->touchedEnv as $name) {
            unset($_ENV[$name], $_SERVER[$name]);
        }
        $this->touchedEnv = [];
    }

    private function setEnv(string $name, string $value): void
    {
        $_ENV[$name] = $value;
        $this->touchedEnv[] = $name;
    }

    private function command(): AggregateCommand
    {
        // Passive collaborators — the parsing methods under test never touch them,
        // so plain stubs (no expectations) are the right tool.
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn(sys_get_temp_dir());

        return new AggregateCommand(
            $this->createStub(Connection::class),
            $this->createStub(Environment::class),
            $this->createStub(MailerInterface::class),
            $kernel,
        );
    }

    private function invoke(AggregateCommand $command, string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionMethod($command, $method);
        $ref->setAccessible(true);

        return $ref->invoke($command, ...$args);
    }

    // ---- envBool: only 1/true/yes/on (case-insensitive) are true --------------

    public function testEnvBoolAcceptsCanonicalTruthyValues(): void
    {
        $command = $this->command();

        foreach (['1', 'true', 'TRUE', 'yes', 'On', 'ON'] as $raw) {
            $this->setEnv('TEST_BOOL', $raw);
            self::assertTrue($this->invoke($command, 'envBool', 'TEST_BOOL', false), "'$raw' should be true");
        }
    }

    public function testEnvBoolRejectsEverythingElseEvenOverridingATrueDefault(): void
    {
        $command = $this->command();

        // A set-but-non-truthy value must win over a true default (e.g. explicitly
        // disabling notifications with APP_NOTIFICATION_ENABLE=0).
        foreach (['0', 'false', 'no', 'off', '2', 'enabled'] as $raw) {
            $this->setEnv('TEST_BOOL', $raw);
            self::assertFalse($this->invoke($command, 'envBool', 'TEST_BOOL', true), "'$raw' should be false");
        }
    }

    public function testEnvBoolFallsBackToDefaultWhenUnset(): void
    {
        $command = $this->command();

        self::assertTrue($this->invoke($command, 'envBool', 'TEST_BOOL_UNSET', true));
        self::assertFalse($this->invoke($command, 'envBool', 'TEST_BOOL_UNSET', false));
    }

    // ---- envInt: positive integers only, otherwise the default ----------------

    public function testEnvIntAcceptsPositiveIntegers(): void
    {
        $command = $this->command();
        $this->setEnv('TEST_INT', '750');

        self::assertSame(750, $this->invoke($command, 'envInt', 'TEST_INT', 500));
    }

    public function testEnvIntRejectsZeroNegativeAndNonNumeric(): void
    {
        $command = $this->command();

        // Zero and negatives are treated as "not a valid threshold" and fall back to
        // the default — pinning this avoids a surprising APP_MIN_ERROR_CODE=0 silently
        // becoming 500.
        foreach (['0', '-5', 'abc', '5xx'] as $raw) {
            $this->setEnv('TEST_INT', $raw);
            self::assertSame(500, $this->invoke($command, 'envInt', 'TEST_INT', 500), "'$raw' should fall back");
        }

        self::assertSame(500, $this->invoke($command, 'envInt', 'TEST_INT_UNSET', 500));
    }

    // ---- envCsv: trimmed, empty segments dropped ------------------------------

    public function testEnvCsvTrimsAndDropsEmptySegments(): void
    {
        $command = $this->command();
        $this->setEnv('TEST_CSV', 'a, b ,,c,');

        self::assertSame(['a', 'b', 'c'], $this->invoke($command, 'envCsv', 'TEST_CSV'));
    }

    public function testEnvCsvReturnsEmptyListWhenUnset(): void
    {
        $command = $this->command();

        self::assertSame([], $this->invoke($command, 'envCsv', 'TEST_CSV_UNSET'));
    }

    // ---- envJson: invalid JSON degrades to the default ------------------------

    public function testEnvJsonReturnsDefaultOnInvalidJson(): void
    {
        $command = $this->command();
        $this->setEnv('TEST_JSON', '{not valid json');

        self::assertSame(['fallback'], $this->invoke($command, 'envJson', 'TEST_JSON', ['fallback']));
    }

    // ---- buildFloatMap: global + numeric per-server overrides -----------------

    public function testBuildFloatMapUsesGlobalDefaultWhenUnset(): void
    {
        $command = $this->command();

        self::assertSame(
            ['global' => 1.5],
            $this->invoke($command, 'buildFloatMap', 'TEST_FM_GLOBAL_UNSET', '1.5', 'TEST_FM_MAP_UNSET'),
        );
    }

    public function testBuildFloatMapMergesNumericOverridesAndSkipsInvalid(): void
    {
        $command = $this->command();
        $this->setEnv('TEST_FM_GLOBAL', '2.5');
        // srv1 int and srv2 numeric-string are kept and coerced to float; the
        // non-numeric "bad" entry is dropped rather than poisoning the map.
        $this->setEnv('TEST_FM_MAP', '{"srv1":3,"srv2":"4.5","bad":"x"}');

        self::assertSame(
            ['global' => 2.5, 'srv1' => 3.0, 'srv2' => 4.5],
            $this->invoke($command, 'buildFloatMap', 'TEST_FM_GLOBAL', '1.5', 'TEST_FM_MAP'),
        );
    }

    // ---- buildNotificationList: only well-formed {hosts,email} rows survive ----

    public function testBuildNotificationListKeepsOnlyCompleteStringRows(): void
    {
        $command = $this->command();
        $this->setEnv(
            'TEST_NL',
            '[{"hosts":"a","email":"a@x"},{"hosts":"b"},{"email":"z@x"},"garbage",{"hosts":"c","email":"c@x"}]',
        );

        self::assertSame(
            [
                ['hosts' => 'a', 'email' => 'a@x'],
                ['hosts' => 'c', 'email' => 'c@x'],
            ],
            $this->invoke($command, 'buildNotificationList', 'TEST_NL'),
        );
    }

    public function testBuildNotificationListReturnsEmptyForNonArrayJson(): void
    {
        $command = $this->command();
        $this->setEnv('TEST_NL', '"just a string"');

        self::assertSame([], $this->invoke($command, 'buildNotificationList', 'TEST_NL'));
    }

    // ---- percentile SQL guards: malformed Pinba values must be nulled ---------

    public function testSafePinbaFloatBuildsRegexValidatedDoubleGuard(): void
    {
        $command = $this->command();

        $sql = $this->invoke($command, 'safePinbaFloat', 'p90');

        self::assertIsString($sql);
        self::assertStringContainsString("TRIM(CONVERT(p90, CHAR)) REGEXP '^-?([0-9]+(\\.[0-9]+)?|\\.[0-9]+)([eE][+-]?[0-9]+)?$'", $sql);
        self::assertStringContainsString('CAST(TRIM(CONVERT(p90, CHAR)) AS DOUBLE)', $sql);
        self::assertMatchesRegularExpression('/-1\\.7976931348623\\d*E\\+308/', $sql);
        self::assertMatchesRegularExpression('/[^-]1\\.7976931348623\\d*E\\+308/', $sql);
        self::assertStringContainsString('ELSE NULL', $sql);
    }

    public function testSafePinbaPercentilesBuildsAliasedGuardedSelectList(): void
    {
        $command = $this->command();

        $sql = $this->invoke($command, 'safePinbaPercentiles');

        self::assertIsString($sql);
        self::assertStringContainsString('AS p90', $sql);
        self::assertStringContainsString('AS p95', $sql);
        self::assertStringContainsString('AS p99', $sql);
        self::assertSame(3, substr_count($sql, 'REGEXP'));
        self::assertSame(3, substr_count($sql, 'ELSE NULL'));
    }

    // ---- isNotIgnore: unanchored regex match against the ignore list ----------

    public function testIsNotIgnoreMatchesIgnorePatternsAsUnanchoredRegex(): void
    {
        $command = $this->command();
        $this->setConfigIgnore($command, ['^prod', 'staging']);

        // Matched (i.e. ignored) → isNotIgnore returns false.
        self::assertFalse($this->invoke($command, 'isNotIgnore', 'prod-web-1'), 'anchored ^prod matches');
        self::assertFalse($this->invoke($command, 'isNotIgnore', 'staging-2'), 'staging matches at start');
        self::assertFalse($this->invoke($command, 'isNotIgnore', 'my-staging-box'), 'unanchored: substring matches');

        // Not matched → isNotIgnore returns true (host is alerted on).
        self::assertTrue($this->invoke($command, 'isNotIgnore', 'app-live'));
        self::assertTrue($this->invoke($command, 'isNotIgnore', 'web-production'), '^prod does not match mid-string');
    }

    public function testIsNotIgnoreReturnsTrueWhenIgnoreListEmpty(): void
    {
        $command = $this->command();
        $this->setConfigIgnore($command, []);

        self::assertTrue($this->invoke($command, 'isNotIgnore', 'anything'));
    }

    /** @param list<string> $ignore */
    private function setConfigIgnore(AggregateCommand $command, array $ignore): void
    {
        $config = new AggregateConfig(
            recordsLifetime: 'P1M',
            aggregationPeriod: 'PT15M',
            longRequestTime: ['global' => 1.5],
            heavyRequest: ['global' => 30000.0],
            heavyCpuRequest: ['global' => 1.0],
            notificationEnable: true,
            notificationSender: 'noreply@pinboard',
            notificationGlobalEmail: 'ops@example.com',
            notificationIgnore: $ignore,
            notificationList: [],
            reqTimeBorder: ['global' => 1.5],
            minErrorCode: 500,
        );

        $ref = new \ReflectionProperty($command, 'config');
        $ref->setAccessible(true);
        $ref->setValue($command, $config);
    }
}
