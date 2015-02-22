<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20150222233821 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `ipm_req_time_details` ADD `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `ipm_req_time_details` DROP `id`;
        ");
    }
}
