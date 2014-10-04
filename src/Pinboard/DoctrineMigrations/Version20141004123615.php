<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20141004123615 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `ipm_report_by_server_name` ADD INDEX `irsn_ca` (`created_at`);
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `ipm_report_by_server_name` DROP INDEX `irsn_ca`;
        ");
    }
}
