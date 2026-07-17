<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717125500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Widen aggregated percentile columns to DOUBLE for MySQL 8.4 strict mode compatibility';
    }

    public function up(Schema $schema): void
    {
        foreach ([
            'ipm_report_by_hostname',
            'ipm_report_by_hostname_and_server',
            'ipm_report_by_server_name',
            'ipm_tag_info',
        ] as $table) {
            foreach (['p90', 'p95', 'p99'] as $column) {
                $this->addSql(sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` DOUBLE DEFAULT NULL',
                    $table,
                    $column
                ));
            }
        }
    }

    public function down(Schema $schema): void
    {
        foreach ([
            'ipm_report_by_hostname',
            'ipm_report_by_hostname_and_server',
            'ipm_report_by_server_name',
            'ipm_tag_info',
        ] as $table) {
            foreach (['p90', 'p95', 'p99'] as $column) {
                $this->addSql(sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` FLOAT DEFAULT NULL',
                    $table,
                    $column
                ));
            }
        }
    }
}
