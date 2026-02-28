<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260123060037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personnage (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, class VARCHAR(50) NOT NULL, spec VARCHAR(80) NOT NULL, race VARCHAR(50) DEFAULT NULL, profession1 VARCHAR(50) DEFAULT NULL, profession2 VARCHAR(50) DEFAULT NULL, combat_role VARCHAR(255) DEFAULT NULL, is_main TINYINT DEFAULT 0 NOT NULL, is_public TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_6AEA486DA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE personnage ADD CONSTRAINT FK_6AEA486DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personnage DROP FOREIGN KEY FK_6AEA486DA76ED395');
        $this->addSql('DROP TABLE personnage');
    }
}
