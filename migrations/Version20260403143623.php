<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403143623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE guide (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, content LONGTEXT NOT NULL, featured_image VARCHAR(255) DEFAULT NULL, is_published TINYINT NOT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category_id INT NOT NULL, author_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_CA9EC735989D9B62 (slug), INDEX IDX_CA9EC73512469DE2 (category_id), INDEX IDX_CA9EC735F675F31B (author_id), INDEX idx_guide_slug (slug), INDEX idx_guide_is_published (is_published), INDEX idx_guide_published_at (published_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE guide_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, slug VARCHAR(180) NOT NULL, description LONGTEXT DEFAULT NULL, position INT NOT NULL, is_active TINYINT NOT NULL, icon VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, parent_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_33CDA7A1989D9B62 (slug), INDEX IDX_33CDA7A1727ACA70 (parent_id), INDEX idx_guide_category_slug (slug), INDEX idx_guide_category_position (position), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE guide ADD CONSTRAINT FK_CA9EC73512469DE2 FOREIGN KEY (category_id) REFERENCES guide_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guide ADD CONSTRAINT FK_CA9EC735F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE guide_category ADD CONSTRAINT FK_33CDA7A1727ACA70 FOREIGN KEY (parent_id) REFERENCES guide_category (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guide DROP FOREIGN KEY FK_CA9EC73512469DE2');
        $this->addSql('ALTER TABLE guide DROP FOREIGN KEY FK_CA9EC735F675F31B');
        $this->addSql('ALTER TABLE guide_category DROP FOREIGN KEY FK_33CDA7A1727ACA70');
        $this->addSql('DROP TABLE guide');
        $this->addSql('DROP TABLE guide_category');
    }
}
