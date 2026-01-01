<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229181528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE raid_event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(120) NOT NULL, starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, created_by_id INT NOT NULL, INDEX IDX_EA273CCAB03A8386 (created_by_id), INDEX idx_raid_starts_at (starts_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE raid_signup (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(10) NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, raid_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_7D718A3C9C55ABC9 (raid_id), INDEX IDX_7D718A3CA76ED395 (user_id), UNIQUE INDEX uniq_raid_user (raid_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE raid_event ADD CONSTRAINT FK_EA273CCAB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE raid_signup ADD CONSTRAINT FK_7D718A3C9C55ABC9 FOREIGN KEY (raid_id) REFERENCES raid_event (id)');
        $this->addSql('ALTER TABLE raid_signup ADD CONSTRAINT FK_7D718A3CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE raid_event DROP FOREIGN KEY FK_EA273CCAB03A8386');
        $this->addSql('ALTER TABLE raid_signup DROP FOREIGN KEY FK_7D718A3C9C55ABC9');
        $this->addSql('ALTER TABLE raid_signup DROP FOREIGN KEY FK_7D718A3CA76ED395');
        $this->addSql('DROP TABLE raid_event');
        $this->addSql('DROP TABLE raid_signup');
    }
}
