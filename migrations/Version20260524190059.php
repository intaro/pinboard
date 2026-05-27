<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524190059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add hosts regexp column to user table for per-user server access filtering';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `user` ADD hosts VARCHAR(500) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `user` DROP COLUMN hosts");
    }
}
