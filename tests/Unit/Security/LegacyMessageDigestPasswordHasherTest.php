<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\LegacyMessageDigestPasswordHasher;
use PHPUnit\Framework\TestCase;

/**
 * The legacy hasher must stay bit-for-bit compatible with the sha512/5000-iteration
 * base64 scheme used by pre-2.x (Silex-era) Pinboard, otherwise every existing
 * stored password stops verifying and users are silently locked out. These tests
 * pin the wire format (known vectors) and the intentional saltless determinism so a
 * well-meaning "improvement" (changing the iteration count, the concatenation order,
 * or adding a salt) cannot land unnoticed.
 */
final class LegacyMessageDigestPasswordHasherTest extends TestCase
{
    // Canonical Symfony MessageDigestPasswordHasher output: sha512, 5000 iterations,
    // empty salt, base64-encoded. Recomputing these requires the exact legacy recipe.
    private const KNOWN_VECTORS = [
        'pinboard' => 'dbKeJ2b0FONX7r6SpuxBoEezcRLmwf6WTLoPuWTcasv7gi9s/jcUOuxWCxhmn2qSbR+FLAf62PlnpWTtRuBNmw==',
        's3cr3t' => 'nqQ1mA6Y/pt0XdIw/d1G7Hc4Ce9BEqmSSxMMLY3CkE3iP9OQq/nnLCZCQxntH6jMNqRTsJDSTwHW4+PlwTZLVA==',
        '' => 'Vdbm7LMUbfL6Wm1WDrTLshjJD5r50nPo/ABPfoCRBAff6IdF0TP160zGRILuVYIHXyoDyhTV/BvquNQgEY2uew==',
    ];

    public function testHashMatchesKnownLegacyVectors(): void
    {
        $hasher = new LegacyMessageDigestPasswordHasher();

        foreach (self::KNOWN_VECTORS as $plain => $expected) {
            self::assertSame(
                $expected,
                $hasher->hash($plain),
                sprintf('Legacy hash for %s must not change', var_export($plain, true))
            );
        }
    }

    public function testHashIsDeterministicAndSaltless(): void
    {
        $hasher = new LegacyMessageDigestPasswordHasher();

        // Two independent hashes of the same password must be identical — the scheme
        // has no random salt on purpose, which is what lets stored hashes be verified.
        self::assertSame($hasher->hash('pinboard'), $hasher->hash('pinboard'));
    }

    public function testVerifyAcceptsCorrectPassword(): void
    {
        $hasher = new LegacyMessageDigestPasswordHasher();

        self::assertTrue($hasher->verify(self::KNOWN_VECTORS['pinboard'], 'pinboard'));
        self::assertTrue($hasher->verify(self::KNOWN_VECTORS['s3cr3t'], 's3cr3t'));
    }

    public function testVerifyRejectsWrongPassword(): void
    {
        $hasher = new LegacyMessageDigestPasswordHasher();

        self::assertFalse($hasher->verify(self::KNOWN_VECTORS['pinboard'], 'Pinboard'));
        self::assertFalse($hasher->verify(self::KNOWN_VECTORS['pinboard'], 'wrong'));
        self::assertFalse($hasher->verify(self::KNOWN_VECTORS['pinboard'], ''));
    }

    public function testVerifyRejectsMalformedHashWithoutError(): void
    {
        $hasher = new LegacyMessageDigestPasswordHasher();

        // A truncated / garbage stored hash must simply fail to verify (hash_equals
        // handles the length mismatch), never throw.
        self::assertFalse($hasher->verify('not-a-real-hash', 'pinboard'));
        self::assertFalse($hasher->verify('', 'pinboard'));
    }

    public function testNeedsRehashIsAlwaysFalse(): void
    {
        $hasher = new LegacyMessageDigestPasswordHasher();

        // Legacy hashes are frozen: rehashing would destroy compatibility, so the
        // hasher must never ask the framework to rehash on login.
        self::assertFalse($hasher->needsRehash(self::KNOWN_VECTORS['pinboard']));
        self::assertFalse($hasher->needsRehash('anything'));
    }
}
