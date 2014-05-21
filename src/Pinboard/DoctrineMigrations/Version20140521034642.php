<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140521034642 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` ADD req_time_median float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` ADD req_time_median float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` ADD req_time_median float DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` DROP req_time_median");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` DROP req_time_median");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` DROP req_time_median");
    }
}
