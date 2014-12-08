<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141130114701 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `ipm_timer` ADD INDEX `it_rica` (`request_id`, `created_at`);
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE `ipm_timer DROP INDEX `it_rica`;
        ");
    }
}
