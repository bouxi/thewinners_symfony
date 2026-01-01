<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229151753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_one_id INT NOT NULL, user_two_id INT NOT NULL, INDEX IDX_8A8E26E99EC8D52E (user_one_id), INDEX IDX_8A8E26E9F59432E1 (user_two_id), UNIQUE INDEX uniq_conversation_pair (user_one_id, user_two_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, recipient_read_at DATETIME DEFAULT NULL, conversation_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307FF624B39D (sender_id), INDEX idx_message_read (recipient_read_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E99EC8D52E FOREIGN KEY (user_one_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9F59432E1 FOREIGN KEY (user_two_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E99EC8D52E');
        $this->addSql('ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9F59432E1');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE message');
    }
}
