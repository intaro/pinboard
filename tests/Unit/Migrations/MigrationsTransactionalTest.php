<?php

declare(strict_types=1);

namespace App\Tests\Unit\Migrations;

use Doctrine\Migrations\AbstractMigration;
use PHPUnit\Framework\TestCase;

/**
 * MySQL commits implicitly around every DDL statement, so a transactional
 * migration finds its transaction already gone when doctrine/migrations tries
 * to commit it. doctrine/migrations reports that as a deprecation (it becomes an
 * error in the next major). The `transactional: false` bundle option only
 * shapes newly generated migrations; each class still has to override
 * isTransactional() itself, which is pinned here for every migration.
 */
final class MigrationsTransactionalTest extends TestCase
{
    public function testEveryMigrationIsNonTransactional(): void
    {
        $files = glob(\dirname(__DIR__, 3).'/migrations/Version*.php');
        self::assertNotFalse($files);
        self::assertNotEmpty($files);

        foreach ($files as $file) {
            require_once $file;

            $class = 'DoctrineMigrations\\'.basename($file, '.php');
            self::assertTrue(class_exists($class), $class.' is not declared in '.$file);

            $migration = new \ReflectionClass($class)->newInstanceWithoutConstructor();
            self::assertInstanceOf(AbstractMigration::class, $migration);
            self::assertFalse($migration->isTransactional(), $class.' must override isTransactional() to return false');
        }
    }
}
