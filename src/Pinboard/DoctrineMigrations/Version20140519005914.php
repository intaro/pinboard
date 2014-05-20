<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140519005914 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` ADD timers_cnt INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_status_details` ADD timers_cnt INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_cpu_usage_details` ADD timers_cnt INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_mem_peak_usage_details` ADD timers_cnt INT(11) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` DROP timers_cnt");
        $this->addSql("ALTER TABLE `ipm_status_details` DROP timers_cnt");
        $this->addSql("ALTER TABLE `ipm_cpu_usage_details` DROP timers_cnt");
        $this->addSql("ALTER TABLE `ipm_mem_peak_usage_details` DROP timers_cnt");
    }
}
