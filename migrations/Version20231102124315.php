<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
//        $this->addSql("
//              ....
//            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,__server_name::90,95,99'
//        ");
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
//        $this->addSql("
//            CREATE TABLE `ipm_pinba_tag_info_category_server_server_name` (
//            ...
//            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,server,__server_name::90,95,99'
//        ");
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
//        $this->addSql("
//        ...
//            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,__server_name,__hostname::90,95,99'
//        ");
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
//        $this->addSql("
        // ...
//            ) ENGINE=PINBA DEFAULT CHARSET=latin1 COMMENT='tagN_info:category,server,__server_name,__hostname::90,95,99'
//        ");
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE `ipm_pinba_tag_info_category_server_name`");
        $this->addSql("DROP TABLE `ipm_pinba_tag_info_category_server_server_name`");
        $this->addSql("DROP TABLE `ipm_pinba_tag_info_category_server_name_hostname`");
        $this->addSql("DROP TABLE `ipm_pinba_tag_info_category_server_server_name_hostname`");
    }
}
