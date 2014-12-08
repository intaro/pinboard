<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140519023825 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` ADD request_id INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_req_time_details` ADD mem_peak_usage FLOAT DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` DROP request_id");
        $this->addSql("ALTER TABLE `ipm_req_time_details` DROP mem_peak_usage");
    }
}
