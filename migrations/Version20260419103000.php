<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260419103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add blocked_until column to utilisateur table if missing.';
    }

    public function up(Schema $schema): void
    {
        $columnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateur' AND COLUMN_NAME = 'blocked_until'"
        );

        if ($columnExists === 0) {
            $this->addSql("ALTER TABLE utilisateur ADD blocked_until DATETIME DEFAULT NULL");
        }
    }

    public function down(Schema $schema): void
    {
        $columnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateur' AND COLUMN_NAME = 'blocked_until'"
        );

        if ($columnExists > 0) {
            $this->addSql("ALTER TABLE utilisateur DROP COLUMN blocked_until");
        }
    }
}
