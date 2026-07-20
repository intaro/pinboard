<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260720120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase remaining Pinboard hostname columns to varchar(64)';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        if ($this->hasColumnWithLength('ipm_cpu_usage_details', 'hostname', 32)) {
            $this->addSql('ALTER TABLE `ipm_cpu_usage_details` MODIFY `hostname` varchar(64) DEFAULT NULL');
        }

        $this->recreatePinbaReportByHostnameAndServer(64);
        $this->recreatePinbaReportByHostname(64);
    }

    public function down(Schema $schema): void
    {
        if ($this->hasColumnWithLength('ipm_cpu_usage_details', 'hostname', 64)) {
            $this->addSql('ALTER TABLE `ipm_cpu_usage_details` MODIFY `hostname` varchar(32) DEFAULT NULL');
        }

        $this->recreatePinbaReportByHostnameAndServer(32);
        $this->recreatePinbaReportByHostname(32);
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

    private function recreatePinbaReportByHostnameAndServer(int $hostnameLength): void
    {
        if (!$this->hasColumnWithLength('ipm_pinba_report_by_hostname_and_server_90_95_99', 'hostname', $hostnameLength === 64 ? 32 : 64)) {
            return;
        }

        $this->addSql('DROP TABLE IF EXISTS `ipm_pinba_report_by_hostname_and_server_90_95_99`');
        $this->addSql(sprintf(
            "
            CREATE TABLE `ipm_pinba_report_by_hostname_and_server_90_95_99` (
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `req_time_total` float DEFAULT NULL,
                `req_time_percent` float DEFAULT NULL,
                `req_time_per_sec` float DEFAULT NULL,
                `ru_utime_total` float DEFAULT NULL,
                `ru_utime_percent` float DEFAULT NULL,
                `ru_utime_per_sec` float DEFAULT NULL,
                `ru_stime_total` float DEFAULT NULL,
                `ru_stime_percent` float DEFAULT NULL,
                `ru_stime_per_sec` float DEFAULT NULL,
                `traffic_total` float DEFAULT NULL,
                `traffic_percent` float DEFAULT NULL,
                `traffic_per_sec` float DEFAULT NULL,
                `hostname` varchar(%d) DEFAULT NULL,
                `server_name` varchar(64) DEFAULT NULL,
                `memory_footprint_total` float DEFAULT NULL,
                `memory_footprint_percent` float DEFAULT NULL,
                `req_time_median` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report6:::90,95,99'
        ",
            $hostnameLength,
        ));
    }

    private function recreatePinbaReportByHostname(int $hostnameLength): void
    {
        if (!$this->hasColumnWithLength('ipm_pinba_report_by_hostname_90_95_99', 'hostname', $hostnameLength === 64 ? 32 : 64)) {
            return;
        }

        $this->addSql('DROP TABLE IF EXISTS `ipm_pinba_report_by_hostname_90_95_99`');
        $this->addSql(sprintf(
            "
            CREATE TABLE `ipm_pinba_report_by_hostname_90_95_99` (
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `req_time_total` float DEFAULT NULL,
                `req_time_percent` float DEFAULT NULL,
                `req_time_per_sec` float DEFAULT NULL,
                `ru_utime_total` float DEFAULT NULL,
                `ru_utime_percent` float DEFAULT NULL,
                `ru_utime_per_sec` float DEFAULT NULL,
                `ru_stime_total` float DEFAULT NULL,
                `ru_stime_percent` float DEFAULT NULL,
                `ru_stime_per_sec` float DEFAULT NULL,
                `traffic_total` float DEFAULT NULL,
                `traffic_percent` float DEFAULT NULL,
                `traffic_per_sec` float DEFAULT NULL,
                `hostname` varchar(%d) DEFAULT NULL,
                `memory_footprint_total` float DEFAULT NULL,
                `memory_footprint_percent` float DEFAULT NULL,
                `req_time_median` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report3:::90,95,99'
        ",
            $hostnameLength,
        ));
    }
}
