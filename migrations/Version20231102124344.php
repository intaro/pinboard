<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_tag_info` (
                `category` varchar(64) DEFAULT NULL,
                `group` varchar(64) DEFAULT NULL,
                `server` varchar(64) DEFAULT NULL,
                `server_name` varchar(64) DEFAULT NULL,
                `hostname` varchar(64) DEFAULT NULL,
                `req_count` int(11) DEFAULT NULL,
                `req_per_sec` float DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `hit_per_sec` float DEFAULT NULL,
                `timer_value` float DEFAULT NULL,
                `timer_median` float DEFAULT NULL,
                `ru_utime_value` float DEFAULT NULL,
                `ru_stime_value` float DEFAULT NULL,
                `p90` float DEFAULT NULL,
                `p95` float DEFAULT NULL,
                `p99` float DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->addSql("
            ALTER TABLE `ipm_tag_info` ADD INDEX `iti_shc` (`server_name` , `hostname`, `created_at`);
            ALTER TABLE `ipm_tag_info` ADD INDEX `iti_sc` (`server_name` , `created_at`);
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE `ipm_tag_info`");
    }
}
