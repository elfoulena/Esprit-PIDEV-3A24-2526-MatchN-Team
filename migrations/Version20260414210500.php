<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260414210500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create notification table if missing.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE IF NOT EXISTS notification (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                titre VARCHAR(150) NOT NULL,
                message LONGTEXT NOT NULL,
                lien VARCHAR(255) DEFAULT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX IDX_BF5476CAA76ED395 (user_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );

        $fkExists = (int) $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notification' AND CONSTRAINT_NAME = 'FK_NOTIFICATION_USER'"
        );

        if ($fkExists === 0) {
            $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_NOTIFICATION_USER FOREIGN KEY (user_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS notification');
    }
}
