<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260211070743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make guild_application.personnage_id NOT NULL (schema sync with mapping)';
    }

    public function up(Schema $schema): void
    {
        // Sécurité: en prod ça évite un crash si des NULL existent.
        // En dev DB neuve: ça passera direct.
        $nullCount = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM guild_application WHERE personnage_id IS NULL');

        if ($nullCount > 0) {
            throw new \RuntimeException(sprintf(
                'Impossible de passer personnage_id en NOT NULL : %d candidature(s) ont personnage_id = NULL. ' .
                'Corrige les données (associe un personnage) puis relance.',
                $nullCount
            ));
        }

        $this->addSql('ALTER TABLE guild_application CHANGE personnage_id personnage_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // Revenir en nullable si on rollback
        $this->addSql('ALTER TABLE guild_application CHANGE personnage_id personnage_id INT DEFAULT NULL');
    }
}
