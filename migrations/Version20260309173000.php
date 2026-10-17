<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309173000 extends AbstractMigration
{
    private const array SOURCE_TABLES = [
        'ipm_pinba_report_by_hostname_and_server_90_95_99',
        'ipm_pinba_report_by_server_90_95_99',
        'ipm_pinba_report_by_hostname_90_95_99',
        'ipm_pinba_tag_info_group_server_name',
        'ipm_pinba_tag_info_group_server_server_name',
        'ipm_pinba_tag_info_group_server_name_hostname',
        'ipm_pinba_tag_info_group_server_server_name_hostname',
        'ipm_pinba_tag_info_category_server_name',
        'ipm_pinba_tag_info_category_server_server_name',
        'ipm_pinba_tag_info_category_server_name_hostname',
        'ipm_pinba_tag_info_category_server_server_name_hostname',
    ];

    public function getDescription(): string
    {
        return 'Repair: convert ipm_pinba_* tables from InnoDB to PINBA engine (fixes installations made before migration 20231102124237 was corrected)';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $pinbaAvailable = (bool)$this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.ENGINES WHERE ENGINE='PINBA' AND SUPPORT IN ('YES','DEFAULT')"
        );

        $this->abortIf(!$pinbaAvailable, 'PINBA storage engine is not available in current MySQL instance.');

        // Check every managed source table, including missing tables.
        $pinbaTableCount = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME IN (:tables)
               AND ENGINE = 'PINBA'",
            ['tables' => self::SOURCE_TABLES],
            ['tables' => ArrayParameterType::STRING],
        );

        if (is_numeric($pinbaTableCount) && (int) $pinbaTableCount === count(self::SOURCE_TABLES)) {
            $this->write('ipm_pinba_* tables already use PINBA engine — skipping repair.');
            return;
        }

        $this->dropPinbaSourceTables();
        $this->createPinbaSourceTables();
    }

    public function down(Schema $schema): void
    {
        $this->dropPinbaSourceTables();
    }

    private function dropPinbaSourceTables(): void
    {
        foreach (self::SOURCE_TABLES as $table) {
            $this->addSql(sprintf('DROP TABLE IF EXISTS `%s`', $table));
        }
    }

    private function createPinbaSourceTables(): void
    {
        $this->addSql("
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
                `hostname` varchar(64) DEFAULT NULL,
                `server_name` varchar(64) DEFAULT NULL,
                `memory_footprint_total` float DEFAULT NULL,
                `memory_footprint_percent` float DEFAULT NULL,
                `req_time_median` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report6:::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_report_by_server_90_95_99` (
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
                `server_name` varchar(64) DEFAULT NULL,
                `memory_footprint_total` float DEFAULT NULL,
                `memory_footprint_percent` float DEFAULT NULL,
                `req_time_median` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report2:::90,95,99'
        ");

        $this->addSql("
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
                `hostname` varchar(64) DEFAULT NULL,
                `memory_footprint_total` float DEFAULT NULL,
                `memory_footprint_percent` float DEFAULT NULL,
                `req_time_median` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report3:::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_group_server_name` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:group,__server_name::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_group_server_server_name` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `tag3_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:group,server,__server_name::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_group_server_name_hostname` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `tag3_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:group,__server_name,__hostname::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_group_server_server_name_hostname` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `tag3_value` varchar(64) DEFAULT NULL,
                `tag4_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:group,server,__server_name,__hostname::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_category_server_name` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,__server_name::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_category_server_server_name` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `tag3_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,server,__server_name::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_category_server_name_hostname` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `tag3_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,__server_name,__hostname::90,95,99'
        ");

        $this->addSql("
            CREATE TABLE `ipm_pinba_tag_info_category_server_server_name_hostname` (
                `tag1_value` varchar(64) DEFAULT NULL,
                `tag2_value` varchar(64) DEFAULT NULL,
                `tag3_value` varchar(64) DEFAULT NULL,
                `tag4_value` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `index_value` varchar(256) DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,server,__server_name,__hostname::90,95,99'
        ");
    }
}
