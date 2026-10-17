<?php

declare(strict_types=1);

namespace App\Tests\Unit\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\AbortMigration;
use DoctrineMigrations\Version20260309173000;
use DoctrineMigrations\Version20261017120000;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

require_once __DIR__ . '/../../../migrations/Version20260309173000.php';
require_once __DIR__ . '/../../../migrations/Version20261017120000.php';

final class PinbaSourceRepairTest extends TestCase
{
    public function testOriginalRepairSkipsOnlyWhenAllElevenTablesUsePinba(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::exactly(2))->method('fetchOne')->willReturnOnConsecutiveCalls(1, 11);
        $migration = new Version20260309173000($connection, new NullLogger());
        $migration->up(new Schema());
        self::assertSame([], $migration->getSql());
    }

    public function testOriginalRepairHandlesMixedAndMissingTables(): void
    {
        foreach ([0, 1, 10] as $pinbaTables) {
            $connection = $this->createMock(Connection::class);
            $connection->expects(self::exactly(2))->method('fetchOne')->willReturnOnConsecutiveCalls(1, $pinbaTables);
            $migration = new Version20260309173000($connection, new NullLogger());
            $migration->up(new Schema());
            self::assertCount(22, $migration->getSql());
            self::assertStringContainsString('CREATE TABLE `ipm_pinba_tag_info_group_server_name`', $migration->getSql()[14]->getStatement());
        }
    }

    public function testFollowUpRepairConvertsOnlyIncorrectTablesWithoutRecreatingThem(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('fetchOne')->willReturn(1);
        $connection->expects(self::once())->method('fetchFirstColumn')->willReturn([
            'ipm_pinba_tag_info_group_server_name',
            'ipm_pinba_tag_info_category_server_name',
        ]);
        $migration = new Version20261017120000($connection, new NullLogger());
        $migration->up(new Schema());
        $sql = array_map(static fn ($query): string => $query->getStatement(), $migration->getSql());
        self::assertSame([
            'ALTER TABLE `ipm_pinba_tag_info_group_server_name` ENGINE=PINBA',
            'ALTER TABLE `ipm_pinba_tag_info_category_server_name` ENGINE=PINBA',
        ], $sql);
    }

    public function testFollowUpRepairIsANoOpForHealthyInstallations(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('fetchOne')->willReturn(1);
        $connection->expects(self::once())->method('fetchFirstColumn')->willReturn([]);
        $migration = new Version20261017120000($connection, new NullLogger());
        $migration->up(new Schema());
        self::assertSame([], $migration->getSql());
    }

    public function testFollowUpRepairAbortsWithoutPinba(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('fetchOne')->willReturn(0);
        $connection->expects(self::never())->method('fetchFirstColumn');
        $migration = new Version20261017120000($connection, new NullLogger());
        $this->expectException(AbortMigration::class);
        $migration->up(new Schema());
    }
}
