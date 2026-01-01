<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230044934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application_message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, is_read_by_applicant TINYINT NOT NULL, application_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_5D03354C3E030ACD (application_id), INDEX IDX_5D03354CF624B39D (sender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE application_message ADD CONSTRAINT FK_5D03354C3E030ACD FOREIGN KEY (application_id) REFERENCES guild_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE application_message ADD CONSTRAINT FK_5D03354CF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY `FK_8A8E26E99EC8D52E`');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY `FK_8A8E26E9F59432E1`');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E99EC8D52E FOREIGN KEY (user_one_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9F59432E1 FOREIGN KEY (user_two_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guild_application DROP FOREIGN KEY `FK_E2BAE1E7A76ED395`');
        $this->addSql('ALTER TABLE guild_application ADD reviewed_at DATETIME DEFAULT NULL, ADD admin_note LONGTEXT DEFAULT NULL, ADD reviewed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE guild_application ADD CONSTRAINT FK_E2BAE1E7A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guild_application ADD CONSTRAINT FK_E2BAE1E7FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E2BAE1E7FC6B21F1 ON guild_application (reviewed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_message DROP FOREIGN KEY FK_5D03354C3E030ACD');
        $this->addSql('ALTER TABLE application_message DROP FOREIGN KEY FK_5D03354CF624B39D');
        $this->addSql('DROP TABLE application_message');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E99EC8D52E');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9F59432E1');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT `FK_8A8E26E99EC8D52E` FOREIGN KEY (user_one_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT `FK_8A8E26E9F59432E1` FOREIGN KEY (user_two_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE guild_application DROP FOREIGN KEY FK_E2BAE1E7A76ED395');
        $this->addSql('ALTER TABLE guild_application DROP FOREIGN KEY FK_E2BAE1E7FC6B21F1');
        $this->addSql('DROP INDEX IDX_E2BAE1E7FC6B21F1 ON guild_application');
        $this->addSql('ALTER TABLE guild_application DROP reviewed_at, DROP admin_note, DROP reviewed_by_id');
        $this->addSql('ALTER TABLE guild_application ADD CONSTRAINT `FK_E2BAE1E7A76ED395` FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
