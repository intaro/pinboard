<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\AggregateCommand;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

final class AggregateCommandStorageTimeTest extends TestCase
{
    public function testWritesAndQueriesUtcEvenWithMoscowPhpTimezone(): void
    {
        $originalTimezone = date_default_timezone_get();
        $projectDir = sys_get_temp_dir() . '/pinboard-aggregate-' . bin2hex(random_bytes(6));
        mkdir($projectDir . '/var', 0775, true);
        date_default_timezone_set('Europe/Moscow');
        $before = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $result = $this->createStub(Result::class);
        $result->method('fetchAllAssociative')->willReturn([]);
        $queries = [];
        $statements = [];
        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturnCallback(
            static function (string $sql, array $params = []) use ($result, &$queries): Result {
                $queries[] = [$sql, $params];
                return $result;
            }
        );
        $connection->method('executeStatement')->willReturnCallback(
            static function (string $sql, array $params = []) use (&$statements): int {
                $statements[] = [$sql, $params];
                return 0;
            }
        );
        $kernel = $this->createStub(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn($projectDir);
        $command = new AggregateCommand($connection, $this->createStub(Environment::class), $this->createStub(MailerInterface::class), $kernel);

        try {
            $tester = new CommandTester($command);
            self::assertSame(0, $tester->execute([]), $tester->getDisplay());
            $after = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            $reportStatements = array_filter($statements, static fn (array $item): bool => str_contains($item[0], 'INSERT IGNORE INTO ipm_report_by_hostname'));
            self::assertCount(1, $reportStatements);
            $reportSql = array_values($reportStatements)[0][0];
            self::assertSame(1, preg_match("/'(\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2})' FROM ipm_pinba_report/", $reportSql, $matches));
            if (!isset($matches[1])) {
                self::fail('No UTC timestamp found in report insert SQL.');
            }
            $stored = new \DateTimeImmutable($matches[1], new \DateTimeZone('UTC'));
            self::assertGreaterThanOrEqual($before->getTimestamp(), $stored->getTimestamp());
            self::assertLessThanOrEqual($after->getTimestamp(), $stored->getTimestamp());

            $lifetime = new \DateInterval('P1M');
            $cutoff = $statements[0][1]['created_at'];
            self::assertGreaterThanOrEqual($before->sub($lifetime)->format('Y-m-d H:i:s'), $cutoff);
            self::assertLessThanOrEqual($after->sub($lifetime)->format('Y-m-d H:i:s'), $cutoff);
            foreach ($queries as [$sql, $params]) {
                if (!str_contains($sql, 'created_at >= :created_at')) {
                    continue;
                }
                $lookback = new \DateInterval('PT30M');
                self::assertGreaterThanOrEqual($before->sub($lookback)->format('Y-m-d H:i:s'), $params['created_at']);
                self::assertLessThanOrEqual($after->sub($lookback)->format('Y-m-d H:i:s'), $params['created_at']);
            }
        } finally {
            date_default_timezone_set($originalTimezone);
            unlink($projectDir . '/var/aggregate.lock');
            rmdir($projectDir . '/var');
            rmdir($projectDir);
        }
    }
}
