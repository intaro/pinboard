<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231102124138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TABLE IF NOT EXISTS `ipm_timer` (
                `timer_id` int(11) DEFAULT NULL,
                `request_id` int(11) DEFAULT NULL,
                `hit_count` int(11) DEFAULT NULL,
                `value` float DEFAULT NULL,
                `tag_name` varchar(255) DEFAULT NULL,
                `tag_value` varchar(64) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TABLE `ipm_timer`");
    }
}
