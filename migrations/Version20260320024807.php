<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320024807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_consent (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, version VARCHAR(100) NOT NULL, accepted TINYINT NOT NULL, recorded_at DATETIME NOT NULL, ip_hash VARCHAR(64) DEFAULT NULL, user_agent VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_3B1F161AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_consent ADD CONSTRAINT FK_3B1F161AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_consent DROP FOREIGN KEY FK_3B1F161AA76ED395');
        $this->addSql('DROP TABLE user_consent');
    }
}
