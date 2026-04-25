<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Security\FileUser;
use App\Security\FileUserStorage;
use App\Security\SwitchableUserProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class KernelWiringTest extends KernelTestCase
{
    public function testKernelWiresFileBasedSecurityServices(): void
    {
        $projectDir = sys_get_temp_dir() . '/pinba-kernel-' . bin2hex(random_bytes(4));
        mkdir($projectDir, 0775, true);
        $usersFile = $projectDir . '/config/users.yaml';

        $this->setEnv('APP_AUTH_USER_SOURCE', 'file');
        $this->setEnv('APP_AUTH_USERS_FILE', $usersFile);

        self::ensureKernelShutdown();

        try {
            self::bootKernel();

            $storage = self::getContainer()->get(FileUserStorage::class);
            $provider = self::getContainer()->get(SwitchableUserProvider::class);

            self::assertInstanceOf(FileUserStorage::class, $storage);
            self::assertInstanceOf(SwitchableUserProvider::class, $provider);

            $storage->upsertUser('admin@example.com', 'hashed-password', ['ROLE_ADMIN'], 'pinba.local');

            $user = $provider->loadUserByIdentifier('admin@example.com');

            self::assertInstanceOf(FileUser::class, $user);
            self::assertSame('admin@example.com', $user->getUserIdentifier());
            self::assertSame('hashed-password', $user->getPassword());
            self::assertSame('pinba.local', $user->getHosts());
        } finally {
            self::ensureKernelShutdown();
            $this->removeDirectory($projectDir);
            $this->unsetEnv('APP_AUTH_USER_SOURCE');
            $this->unsetEnv('APP_AUTH_USERS_FILE');
        }
    }

    private function setEnv(string $name, string $value): void
    {
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    private function unsetEnv(string $name): void
    {
        putenv($name);
        unset($_ENV[$name], $_SERVER[$name]);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }
}
