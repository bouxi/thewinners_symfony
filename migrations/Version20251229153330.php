<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229153330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guild_application (id INT AUTO_INCREMENT NOT NULL, class VARCHAR(40) NOT NULL, specialization VARCHAR(60) NOT NULL, playtime VARCHAR(60) DEFAULT NULL, availability VARCHAR(120) DEFAULT NULL, motivation LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, submitted_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_E2BAE1E7A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE guild_application ADD CONSTRAINT FK_E2BAE1E7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guild_application DROP FOREIGN KEY FK_E2BAE1E7A76ED395');
        $this->addSql('DROP TABLE guild_application');
    }
}
