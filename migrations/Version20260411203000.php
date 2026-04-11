<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260411203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize identity columns for tables mapped with ORM GeneratedValue.';
    }

    public function up(Schema $schema): void
    {
        $targets = [
            ['affectation_projet', 'id'],
            ['calendrier_equipe', 'id_calendrier'],
            ['commit_history', 'id_commit'],
            ['competence_f', 'id'],
            ['demande_equipe', 'id_demande'],
            ['demande_participation', 'id_demande'],
            ['discussion', 'id_discussion'],
            ['equipe', 'id_equipe'],
            ['evaluation_part_time', 'id'],
            ['membre_equipe', 'id_membre'],
            ['message', 'id_message'],
            ['message_discussion', 'id_message'],
            ['participation_evenement', 'id_participation'],
            ['reclamation', 'id_reclamation'],
            ['reponse_reclamation', 'id_reponse'],
            ['repository', 'id_repo'],
            ['utilisateur', 'id'],
        ];

        foreach ($targets as [$table, $column]) {
            $this->ensureAutoIncrementPrimaryKey($table, $column);
        }
    }

    public function down(Schema $schema): void
    {
        // Non-reversible safely in production-like environments.
    }

    private function ensureAutoIncrementPrimaryKey(string $table, string $column): void
    {
        $tableExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$table]
        );
        if ($tableExists === 0) {
            return;
        }

        $columnExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        );
        if ($columnExists === 0) {
            return;
        }

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
        if (stripos($extra, 'auto_increment') !== false) {
            return;
        }

        $maxId = (int) $this->connection->fetchOne(sprintf('SELECT COALESCE(MAX(`%s`), 0) FROM `%s`', $column, $table));
        $nextId = $maxId + 1;

        $this->addSql(sprintf('ALTER TABLE `%s` MODIFY `%s` INT NOT NULL AUTO_INCREMENT', $table, $column));
        $this->addSql(sprintf('ALTER TABLE `%s` AUTO_INCREMENT = %d', $table, $nextId));
    }
}
