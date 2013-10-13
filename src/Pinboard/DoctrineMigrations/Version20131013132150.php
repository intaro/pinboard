<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131013132150 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_report_2_by_hostname_and_server` (
              `server_name` varchar(64) DEFAULT NULL,
              `hostname` varchar(32) DEFAULT NULL,
              `req_time_90` float DEFAULT NULL,
              `req_time_95` float DEFAULT NULL,
              `req_time_99` float DEFAULT NULL,
              `req_time_100` float DEFAULT NULL,
              `mem_peak_usage_90` float DEFAULT NULL,
              `mem_peak_usage_95` float DEFAULT NULL,
              `mem_peak_usage_99` float DEFAULT NULL,
              `mem_peak_usage_100` float DEFAULT NULL,
              `doc_size_90` float DEFAULT NULL,
              `doc_size_95` float DEFAULT NULL,
              `doc_size_99` float DEFAULT NULL,
              `doc_size_100` float DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_report_by_hostname` (
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
              `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report3';
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_report_by_hostname_and_server` (
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
              `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report6';
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_report_by_server_name` (
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
              `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report2';
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_status_details` (
              `server_name` varchar(128) DEFAULT NULL,
              `hostname` varchar(32) DEFAULT NULL,
              `script_name` varchar(128) DEFAULT NULL,
              `status` int(11) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_req_time_details` (
              `server_name` varchar(64) DEFAULT NULL,
              `hostname` varchar(32) DEFAULT NULL,
              `script_name` varchar(128) DEFAULT NULL,
              `req_time` float DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_mem_peak_usage_details` (
              `server_name` varchar(64) DEFAULT NULL,
              `hostname` varchar(32) DEFAULT NULL,
              `script_name` varchar(128) DEFAULT NULL,
              `mem_peak_usage` float DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->addSql("
            ALTER TABLE `ipm_report_2_by_hostname_and_server` ADD INDEX `sn_h_c` (`server_name` , `hostname`, `created_at`);
            ALTER TABLE `ipm_report_2_by_hostname_and_server` ADD INDEX `sn_c` (`server_name`, `created_at`);
            ALTER TABLE `ipm_status_details` ADD INDEX `isd_c` (`server_name`, `created_at`);
            ALTER TABLE `ipm_req_time_details` ADD INDEX `irtd_sn_ca` (`server_name`, `created_at`);
            ALTER TABLE `ipm_req_time_details` ADD INDEX `irtd_ca_rt` (`created_at`, `req_time`);
            ALTER TABLE `ipm_mem_peak_usage_details` ADD INDEX `impu_sn_ca` (`server_name`, `created_at`);
            ALTER TABLE `ipm_mem_peak_usage_details` ADD INDEX `impu_ca_mpu` (`created_at`, `mem_peak_usage`);
            ALTER TABLE `ipm_report_by_server_name` ADD INDEX `irsn_sn` (`server_name`);
            ALTER TABLE `ipm_report_by_hostname_and_server` ADD INDEX `irhas_sn_hn` (`server_name`, `hostname`);
        ");

    }

    public function down(Schema $schema)
    {
        $this->addSql("DROP TABLE `ipm_report_2_by_hostname_and_server`;");
        $this->addSql("DROP TABLE `ipm_report_by_hostname`;");
        $this->addSql("DROP TABLE `ipm_report_by_hostname_and_server`;");
        $this->addSql("DROP TABLE `ipm_report_by_server_name`;");
        $this->addSql("DROP TABLE `ipm_status_details`;");
        $this->addSql("DROP TABLE `ipm_req_time_details`;");
        $this->addSql("DROP TABLE `ipm_mem_peak_usage_details`;");
    }
}
