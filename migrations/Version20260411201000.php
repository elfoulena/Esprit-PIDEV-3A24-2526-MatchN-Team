<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260411201000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix identity columns for Doctrine GeneratedValue on evenement/projet/competence.';
    }

    public function up(Schema $schema): void
    {
        $this->ensureAutoIncrementPrimaryKey('evenement', 'id_evenement');
        $this->ensureAutoIncrementPrimaryKey('projet', 'id_projet');
        $this->ensureAutoIncrementPrimaryKey('competence', 'id_competence');
    }

    public function down(Schema $schema): void
    {
        // Intentionally left minimal: reverting identity semantics can break data integrity.
    }

    private function ensureAutoIncrementPrimaryKey(string $table, string $column): void
    {
        $pkCount = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_TYPE = 'PRIMARY KEY'",
            [$table]
        );

        if ($pkCount === 0) {
            $this->addSql(sprintf('ALTER TABLE `%s` ADD PRIMARY KEY (`%s`)', $table, $column));
        }

        $extra = (string) $this->connection->fetchOne(
            "SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );

        if (stripos($extra, 'auto_increment') === false) {
            $maxId = (int) $this->connection->fetchOne(sprintf('SELECT COALESCE(MAX(`%s`), 0) FROM `%s`', $column, $table));
            $nextId = $maxId + 1;

            $this->addSql(sprintf('ALTER TABLE `%s` MODIFY `%s` INT NOT NULL AUTO_INCREMENT', $table, $column));
            $this->addSql(sprintf('ALTER TABLE `%s` AUTO_INCREMENT = %d', $table, $nextId));
        }
    }
}
