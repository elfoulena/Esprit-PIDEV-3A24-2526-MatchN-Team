<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260411194500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create missing projet_competence join table for Projet<->Competence many-to-many relation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS projet_competence (projet_id INT NOT NULL, competence_id INT NOT NULL, INDEX IDX_7FD5D6B1A4B89032 (projet_id), INDEX IDX_7FD5D6B11576ED5D (competence_id), PRIMARY KEY(projet_id, competence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE projet_competence');
    }
}
