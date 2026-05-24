<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\FileUser;
use App\Security\FileUserStorage;
use App\Security\SwitchableUserProvider;
use PHPUnit\Framework\TestCase;

final class SwitchableUserProviderTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/pinba-switchable-user-' . bin2hex(random_bytes(4));
        mkdir($this->projectDir, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDir);
    }

    public function testLoadUserByIdentifierFromFileStorage(): void
    {
        $storage = new FileUserStorage($this->projectDir, 'config/users.yaml');
        $storage->upsertUser('admin@example.com', 'hashed-password', ['ROLE_ADMIN'], '.*');

        $provider = new SwitchableUserProvider(
            $this->createStub(UserRepository::class),
            $storage,
            'file'
        );

        $user = $provider->loadUserByIdentifier('admin@example.com');

        self::assertInstanceOf(FileUser::class, $user);
        self::assertSame('admin@example.com', $user->getUserIdentifier());
        self::assertSame('hashed-password', $user->getPassword());
        self::assertSame('.*', $user->getHosts());
        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testLoadUserByIdentifierFromDatabase(): void
    {
        $user = (new User())
            ->setEmail('admin@example.com')
            ->setPassword('hashed-password')
            ->setRoles(['ROLE_ADMIN']);

        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'admin@example.com'])
            ->willReturn($user);

        $provider = new SwitchableUserProvider(
            $repository,
            new FileUserStorage($this->projectDir, 'config/users.yaml'),
            'db'
        );

        self::assertSame($user, $provider->loadUserByIdentifier('admin@example.com'));
    }

    public function testUpgradePasswordPersistsToFileStorage(): void
    {
        $storage = new FileUserStorage($this->projectDir, 'config/users.yaml');
        $storage->upsertUser('admin@example.com', 'old-password', ['ROLE_ADMIN']);

        $provider = new SwitchableUserProvider(
            $this->createStub(UserRepository::class),
            $storage,
            'file'
        );

        $provider->upgradePassword(new FileUser('admin@example.com', 'old-password', ['ROLE_ADMIN']), 'new-password');

        self::assertSame('new-password', $storage->loadUsers()['admin@example.com']['password']);
    }

    public function testUpgradePasswordPersistsToDatabase(): void
    {
        $user = (new User())
            ->setEmail('admin@example.com')
            ->setPassword('old-password')
            ->setRoles(['ROLE_ADMIN']);

        $repository = $this->createMock(UserRepository::class);
        $repository->expects(self::once())
            ->method('upgradePassword')
            ->with($user, 'new-password');

        $provider = new SwitchableUserProvider(
            $repository,
            new FileUserStorage($this->projectDir, 'config/users.yaml'),
            'db'
        );

        $provider->upgradePassword($user, 'new-password');
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
