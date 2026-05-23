<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE `ipm_req_time_details` ADD `id` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE `ipm_req_time_details` DROP `id`;
        ");
    }
}
