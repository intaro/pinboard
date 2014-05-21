<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140521134244 extends AbstractMigration
{
    public function up(Schema $schema)
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
                    `index_value` varchar(256) DEFAULT NULL
                    ,`p90` float DEFAULT NULL,
                    `p95` float DEFAULT NULL,
                    `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report6:::90,95,99';
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
                    `index_value` varchar(256) DEFAULT NULL
                    ,`p90` float DEFAULT NULL,
                    `p95` float DEFAULT NULL,
                    `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report2:::90,95,99';
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
                    `index_value` varchar(256) DEFAULT NULL
                    ,`p90` float DEFAULT NULL,
                    `p95` float DEFAULT NULL,
                    `p99` float DEFAULT NULL
            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='report3:::90,95,99';
        ");

        $this->addSql("ALTER TABLE `ipm_report_by_hostname` ADD p90 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` ADD p95 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` ADD p99 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` ADD p90 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` ADD p95 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` ADD p99 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` ADD p90 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` ADD p95 float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` ADD p99 float DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        $this->addSql("DROP TABLE `ipm_pinba_report_by_hostname_and_server_90_95_99`");
        $this->addSql("DROP TABLE `ipm_pinba_report_by_server_90_95_99`");
        $this->addSql("DROP TABLE `ipm_pinba_report_by_hostname_90_95_99`");

        $this->addSql("ALTER TABLE `ipm_report_by_hostname` DROP p90");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` DROP p95");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` DROP p99");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` DROP p90");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` DROP p95");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` DROP p99");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` DROP p90");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` DROP p95");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` DROP p99");
    }
}
