<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` ADD timers_cnt INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_status_details` ADD timers_cnt INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_cpu_usage_details` ADD timers_cnt INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_mem_peak_usage_details` ADD timers_cnt INT(11) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` DROP timers_cnt");
        $this->addSql("ALTER TABLE `ipm_status_details` DROP timers_cnt");
        $this->addSql("ALTER TABLE `ipm_cpu_usage_details` DROP timers_cnt");
        $this->addSql("ALTER TABLE `ipm_mem_peak_usage_details` DROP timers_cnt");
    }
}
