<?php

namespace Pinboard\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140519022332 extends AbstractMigration
{
    public function up(Schema $schema)
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

    public function down(Schema $schema)
    {
        $this->addSql("DROP TABLE `ipm_timer`");
    }
}
