<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Repair remaining ipm_pinba_* source tables skipped by the single-table engine check';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $pinbaAvailable = (bool) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.ENGINES WHERE ENGINE='PINBA' AND SUPPORT IN ('YES','DEFAULT')"
        );
        $this->abortIf(!$pinbaAvailable, 'PINBA storage engine is not available in current MySQL instance.');

        $tables = $this->connection->fetchFirstColumn(
            "SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND LEFT(TABLE_NAME, 10) = 'ipm_pinba_'
               AND TABLE_TYPE = 'BASE TABLE'
               AND ENGINE <> 'PINBA'"
        );

        foreach ($tables as $table) {
            if (!is_string($table)) {
                continue;
            }
            // Preserve existing column definitions and Pinba report comments.
            $this->addSql('ALTER TABLE `' . str_replace('`', '``', $table) . '` ENGINE=PINBA');
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Restoring the incorrect source storage engine would break aggregation.');
    }
}
