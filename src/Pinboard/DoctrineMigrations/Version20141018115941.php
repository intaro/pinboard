<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141018115941 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_status_details` ADD INDEX `isd_hostname` (`hostname`);");
        $this->addSql("ALTER TABLE `ipm_status_details` ADD INDEX `isd_status` (`status`);");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_status_details` DROP INDEX `isd_hostname`;");
        $this->addSql("ALTER TABLE `ipm_status_details` DROP INDEX `isd_status`;");
    }
}
