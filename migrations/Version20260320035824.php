<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320035824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_consent ADD privacy_accepted TINYINT DEFAULT 0 NOT NULL, ADD terms_accepted TINYINT DEFAULT 0 NOT NULL, ADD cookies_accepted TINYINT DEFAULT 0 NOT NULL, ADD accepted_at DATETIME DEFAULT NULL, ADD cookie_choice VARCHAR(50) DEFAULT NULL, DROP type, DROP version, DROP accepted, DROP recorded_at, DROP ip_hash, DROP user_agent');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_consent ADD type VARCHAR(50) NOT NULL, ADD version VARCHAR(100) NOT NULL, ADD accepted TINYINT NOT NULL, ADD recorded_at DATETIME NOT NULL, ADD ip_hash VARCHAR(64) DEFAULT NULL, ADD user_agent VARCHAR(255) DEFAULT NULL, DROP privacy_accepted, DROP terms_accepted, DROP cookies_accepted, DROP accepted_at, DROP cookie_choice');
    }
}
