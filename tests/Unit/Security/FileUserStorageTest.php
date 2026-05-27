<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Security\FileUserStorage;
use PHPUnit\Framework\TestCase;

final class FileUserStorageTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/pinba-file-user-' . bin2hex(random_bytes(4));
        mkdir($this->projectDir, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDir);
    }

    public function testLoadUsersReturnsEmptyArrayWhenFileIsMissing(): void
    {
        $storage = new FileUserStorage($this->projectDir, 'config/users.yaml');

        self::assertSame([], $storage->loadUsers());
    }

    public function testUpsertUserPersistsRecordWithDefaultRole(): void
    {
        $storage = new FileUserStorage($this->projectDir, 'config/users.yaml');
        $storage->upsertUser('admin@example.com', 'hashed-password', [], 'example.local');

        self::assertSame(
            [
                'admin@example.com' => [
                    'password' => 'hashed-password',
                    'roles' => ['ROLE_USER'],
                    'hosts' => 'example.local',
                ],
            ],
            $storage->loadUsers()
        );
    }

    public function testReplaceUsersOverwritesEntireCollection(): void
    {
        $storage = new FileUserStorage($this->projectDir, 'config/users.yaml');
        $storage->replaceUsers([
            'admin@example.com' => [
                'password' => 'hashed-password',
                'roles' => ['ROLE_ADMIN'],
            ],
        ]);

        self::assertSame(
            [
                'admin@example.com' => [
                    'password' => 'hashed-password',
                    'roles' => ['ROLE_ADMIN'],
                ],
            ],
            $storage->loadUsers()
        );
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
            if (!$item instanceof \SplFileInfo) {
                continue;
            }
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }
}
