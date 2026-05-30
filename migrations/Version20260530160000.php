<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260530160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at index for ipm_tag_info retention cleanup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE `ipm_tag_info` ADD INDEX `iti_c` (`created_at`);
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE `ipm_tag_info` DROP INDEX `iti_c`;
        ");
    }
}
