<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` ADD req_time_median float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` ADD req_time_median float DEFAULT NULL");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` ADD req_time_median float DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `ipm_report_by_hostname` DROP req_time_median");
        $this->addSql("ALTER TABLE `ipm_report_by_hostname_and_server` DROP req_time_median");
        $this->addSql("ALTER TABLE `ipm_report_by_server_name` DROP req_time_median");
    }
}
