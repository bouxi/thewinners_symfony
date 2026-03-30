<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260320035628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_consent DROP INDEX IDX_3B1F161AA76ED395, ADD UNIQUE INDEX UNIQ_3B1F161AA76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_consent DROP FOREIGN KEY `FK_3B1F161AA76ED395`');
        $this->addSql('ALTER TABLE user_consent CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_consent ADD CONSTRAINT FK_3B1F161AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_consent DROP INDEX UNIQ_3B1F161AA76ED395, ADD INDEX IDX_3B1F161AA76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_consent DROP FOREIGN KEY FK_3B1F161AA76ED395');
        $this->addSql('ALTER TABLE user_consent CHANGE user_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_consent ADD CONSTRAINT `FK_3B1F161AA76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE SET NULL');
    }
}
