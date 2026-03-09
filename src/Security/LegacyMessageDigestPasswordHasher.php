<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class LegacyMessageDigestPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return $this->encodeLegacyPassword($plainPassword);
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return hash_equals($hashedPassword, $this->encodeLegacyPassword($plainPassword));
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }

    private function encodeLegacyPassword(string $plainPassword): string
    {
        $salted = $plainPassword;
        $digest = hash('sha512', $salted, true);

        for ($i = 1; $i < 5000; ++$i) {
            $digest = hash('sha512', $digest . $salted, true);
        }

        return base64_encode($digest);
    }
}
