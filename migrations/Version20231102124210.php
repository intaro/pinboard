<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` ADD request_id INT(11) DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_req_time_details` ADD mem_peak_usage FLOAT DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `ipm_req_time_details` DROP request_id");
        $this->addSql("ALTER TABLE `ipm_req_time_details` DROP mem_peak_usage");
    }
}
