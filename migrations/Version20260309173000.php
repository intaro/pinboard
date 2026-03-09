<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Switch ipm_pinba_* source tables to PINBA engine with legacy comments';
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
        $this->dropPinbaSourceTables();
        $this->createPinbaSourceTables();
    }

    public function down(Schema $schema): void
    {
        $this->dropPinbaSourceTables();
    }

    private function dropPinbaSourceTables(): void
    {
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_report_by_hostname_and_server_90_95_99`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_report_by_server_90_95_99`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_report_by_hostname_90_95_99`");

        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_group_server_name`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_group_server_server_name`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_group_server_name_hostname`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_group_server_server_name_hostname`");

        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_category_server_name`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_category_server_server_name`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_category_server_name_hostname`");
        $this->addSql("DROP TABLE IF EXISTS `ipm_pinba_tag_info_category_server_server_name_hostname`");
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
                `hostname` varchar(32) DEFAULT NULL,
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
                `hostname` varchar(32) DEFAULT NULL,
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
