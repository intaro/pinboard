<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Entity\User;
use App\Security\FileUser;
use App\Utils\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsAccessTest extends TestCase
{
    // ── normalizeHostsConfig ──────────────────────────────────────────────────

    public function testNormalizeHostsConfigReturnsNullForUnrestrictedValues(): void
    {
        self::assertNull(Utils::normalizeHostsConfig(null));
        self::assertNull(Utils::normalizeHostsConfig(''));
        self::assertNull(Utils::normalizeHostsConfig('.*'));
        self::assertNull(Utils::normalizeHostsConfig('  .*  '));
        self::assertNull(Utils::normalizeHostsConfig([]));
        self::assertNull(Utils::normalizeHostsConfig(['.*', '', '  ']));
        self::assertNull(Utils::normalizeHostsConfig([123, false]));
    }

    public function testNormalizeHostsConfigKeepsSingleStringPattern(): void
    {
        self::assertSame('site-a\.com', Utils::normalizeHostsConfig('site-a\.com'));
        self::assertSame('site-a\.com', Utils::normalizeHostsConfig('  site-a\.com  '));
    }

    public function testNormalizeHostsConfigCombinesLegacyArrayOfPatterns(): void
    {
        self::assertSame(
            '(^site-a\.com$)|(^site-b\.com$)',
            Utils::normalizeHostsConfig(['^site-a\.com$', '^site-b\.com$'])
        );
    }

    public function testNormalizeHostsConfigDropsWildcardEntriesFromArray(): void
    {
        self::assertSame('site-a\.com', Utils::normalizeHostsConfig(['.*', 'site-a\.com']));
    }

    public function testNormalizedLegacyArrayRestrictsAccessCorrectly(): void
    {
        $hosts = Utils::normalizeHostsConfig(['^site-a\.com$', '^site-b\.com$']);
        $user = new FileUser('u@example.com', 'h', ['ROLE_USER'], $hosts);

        self::assertTrue(Utils::userCanAccessServer($user, 'site-a.com'));
        self::assertTrue(Utils::userCanAccessServer($user, 'site-b.com'));
        self::assertFalse(Utils::userCanAccessServer($user, 'site-c.com'));
        self::assertFalse(Utils::userCanAccessServer($user, 'evil-site-a.com'));
    }

    // ── getUserHostsRegexp ────────────────────────────────────────────────────

    public function testGetUserHostsRegexpReturnsWildcardForNull(): void
    {
        self::assertSame('.*', Utils::getUserHostsRegexp(null));
    }

    public function testGetUserHostsRegexpReturnsWildcardForFileUserWithNullHosts(): void
    {
        $user = new FileUser('user@example.com', 'hash', ['ROLE_USER'], null);

        self::assertSame('.*', Utils::getUserHostsRegexp($user));
    }

    public function testGetUserHostsRegexpReturnsWildcardForFileUserWithWildcard(): void
    {
        $user = new FileUser('user@example.com', 'hash', ['ROLE_USER'], '.*');

        self::assertSame('.*', Utils::getUserHostsRegexp($user));
    }

    public function testGetUserHostsRegexpReturnsPatternForRestrictedFileUser(): void
    {
        $user = new FileUser('user@example.com', 'hash', ['ROLE_USER'], 'site-a\.com|site-b\.com');

        self::assertSame('site-a\.com|site-b\.com', Utils::getUserHostsRegexp($user));
    }

    public function testGetUserHostsRegexpReturnsWildcardForDbUserWithNullHosts(): void
    {
        $user = (new User())->setEmail('user@example.com')->setRoles(['ROLE_USER']);

        self::assertSame('.*', Utils::getUserHostsRegexp($user));
    }

    public function testGetUserHostsRegexpReturnsPatternForRestrictedDbUser(): void
    {
        $user = (new User())->setEmail('user@example.com')->setRoles(['ROLE_USER'])->setHosts('shop\.example\.com');

        self::assertSame('shop\.example\.com', Utils::getUserHostsRegexp($user));
    }

    // ── userCanAccessServer ───────────────────────────────────────────────────

    public function testUserCanAccessServerReturnsTrueForNullUser(): void
    {
        self::assertTrue(Utils::userCanAccessServer(null, 'any.server.com'));
    }

    public function testUserCanAccessServerReturnsTrueWhenHostsIsWildcard(): void
    {
        $user = new FileUser('u@example.com', 'h', ['ROLE_USER'], '.*');

        self::assertTrue(Utils::userCanAccessServer($user, 'anything.example.com'));
    }

    public function testUserCanAccessServerReturnsTrueForMatchingPattern(): void
    {
        $user = new FileUser('u@example.com', 'h', ['ROLE_USER'], 'webshop\.example\.com');

        self::assertTrue(Utils::userCanAccessServer($user, 'webshop.example.com'));
    }

    public function testUserCanAccessServerReturnsFalseForNonMatchingPattern(): void
    {
        $user = new FileUser('u@example.com', 'h', ['ROLE_USER'], 'webshop\.example\.com');

        self::assertFalse(Utils::userCanAccessServer($user, 'other.example.com'));
    }

    public function testUserCanAccessServerSupportsAlternationPattern(): void
    {
        $user = new FileUser('u@example.com', 'h', ['ROLE_USER'], 'site-a\.com|site-b\.com');

        self::assertTrue(Utils::userCanAccessServer($user, 'site-a.com'));
        self::assertTrue(Utils::userCanAccessServer($user, 'site-b.com'));
        self::assertFalse(Utils::userCanAccessServer($user, 'site-c.com'));
    }

    public function testUserCanAccessServerWorksForDbUser(): void
    {
        $user = (new User())->setEmail('u@example.com')->setRoles(['ROLE_USER'])->setHosts('shop\.example\.com');

        self::assertTrue(Utils::userCanAccessServer($user, 'shop.example.com'));
        self::assertFalse(Utils::userCanAccessServer($user, 'other.example.com'));
    }
}
