<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase hostname columns from varchar(32) to varchar(64) in Pinboard-managed tables';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->widenColumn('ipm_report_2_by_hostname_and_server', 'hostname');
        $this->widenColumn('ipm_report_by_hostname', 'hostname');
        $this->widenColumn('ipm_report_by_hostname_and_server', 'hostname');
        $this->widenColumn('ipm_status_details', 'hostname');
        $this->widenColumn('ipm_req_time_details', 'hostname');
        $this->widenColumn('ipm_mem_peak_usage_details', 'hostname');
        $this->widenColumn('ipm_pinba_report_by_hostname_and_server_90_95_99', 'hostname');
        $this->widenColumn('ipm_pinba_report_by_hostname_90_95_99', 'hostname');
    }

    public function down(Schema $schema): void
    {
        $this->narrowColumn('ipm_report_2_by_hostname_and_server', 'hostname');
        $this->narrowColumn('ipm_report_by_hostname', 'hostname');
        $this->narrowColumn('ipm_report_by_hostname_and_server', 'hostname');
        $this->narrowColumn('ipm_status_details', 'hostname');
        $this->narrowColumn('ipm_req_time_details', 'hostname');
        $this->narrowColumn('ipm_mem_peak_usage_details', 'hostname');
        $this->narrowColumn('ipm_pinba_report_by_hostname_and_server_90_95_99', 'hostname');
        $this->narrowColumn('ipm_pinba_report_by_hostname_90_95_99', 'hostname');
    }

    private function widenColumn(string $table, string $column): void
    {
        if ($this->hasColumnWithLength($table, $column, 32)) {
            $this->addSql(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` varchar(64) DEFAULT NULL',
                $table,
                $column,
            ));
        }
    }

    private function narrowColumn(string $table, string $column): void
    {
        if ($this->hasColumnWithLength($table, $column, 64)) {
            $this->addSql(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` varchar(32) DEFAULT NULL',
                $table,
                $column,
            ));
        }
    }

    private function hasColumnWithLength(string $table, string $column, int $length): bool
    {
        $actualLength = $this->connection->fetchOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table
               AND COLUMN_NAME = :column',
            [
                'table' => $table,
                'column' => $column,
            ],
        );

        return is_numeric($actualLength) && (int) $actualLength === $length;
    }
}
