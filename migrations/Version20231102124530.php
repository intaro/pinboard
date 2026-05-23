<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE `ipm_timer` ADD INDEX `it_rica` (`request_id`, `created_at`);
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            ALTER TABLE `ipm_timer DROP INDEX `it_rica`;
        ");
    }
}
