<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210190933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Safely ensure guild_application.personnage_id is nullable + FK/index exists, and update messenger_messages indexes (idempotent).';
    }

    public function up(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        /**
         * ------------------------------------------------------------
         * 1) guild_application.personnage_id : nullable + index + FK
         * ------------------------------------------------------------
         */
        if ($sm->tablesExist(['guild_application'])) {
            // 1.a) Colonne personnage_id : doit exister + être NULLABLE
            $cols = $sm->listTableColumns('guild_application');

            if (!isset($cols['personnage_id'])) {
                // Si elle n'existe pas, on la crée en NULLABLE pour ne pas casser les données
                $this->addSql('ALTER TABLE guild_application ADD personnage_id INT DEFAULT NULL');
                $sm = $this->connection->createSchemaManager();
                $cols = $sm->listTableColumns('guild_application');
            } else {
                // Si elle existe mais est NOT NULL, on la passe en NULLABLE
                if ($cols['personnage_id']->getNotnull() === true) {
                    $this->addSql('ALTER TABLE guild_application CHANGE personnage_id personnage_id INT NOT NULL');
                }
            }

            // 1.b) Index personnage_id
            $indexes = $sm->listTableIndexes('guild_application');
            if (!isset($indexes['idx_e2bae1e75e315342'])) {
                $this->addSql('CREATE INDEX IDX_E2BAE1E75E315342 ON guild_application (personnage_id)');
            }

            // 1.c) FK personnage_id -> personnage(id) (nom UNIQUE au niveau DB)
            $fkExists = (int) $this->connection->fetchOne("
                SELECT COUNT(*)
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'guild_application'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME = 'FK_E2BAE1E75E315342'
            ");

            // On n'ajoute la FK que si :
            // - elle n'existe pas déjà
            // - ET la table 'personnage' existe (sinon MySQL explose)
            if ($fkExists === 0 && $sm->tablesExist(['personnage'])) {
                $this->addSql(
                    'ALTER TABLE guild_application
                     ADD CONSTRAINT FK_E2BAE1E75E315342
                     FOREIGN KEY (personnage_id) REFERENCES personnage (id) ON DELETE RESTRICT'
                );
            }
        }

        /**
         * ------------------------------------------------------------
         * 2) messenger_messages : refonte indexes (safe)
         * ------------------------------------------------------------
         */
        if ($sm->tablesExist(['messenger_messages'])) {
            $mmIndexes = $sm->listTableIndexes('messenger_messages');

            // On supprime les anciens indexes s'ils existent
            if (isset($mmIndexes['idx_75ea56e016ba31db'])) {
                $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
            }
            if (isset($mmIndexes['idx_75ea56e0e3bd61ce'])) {
                $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
            }
            if (isset($mmIndexes['idx_75ea56e0fb7336f0'])) {
                $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
            }

            // Index composite attendu
            $mmIndexes = $this->connection->createSchemaManager()->listTableIndexes('messenger_messages');
            if (!isset($mmIndexes['idx_75ea56e0fb7336f0e3bd61ce16ba31dbbf396750'])) {
                $this->addSql(
                    'CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750
                     ON messenger_messages (queue_name, available_at, delivered_at, id)'
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        // down "safe" : on essaie de revenir comme l'ancienne migration, sans casser si déjà différent
        if ($sm->tablesExist(['guild_application'])) {
            $fkExists = (int) $this->connection->fetchOne("
                SELECT COUNT(*)
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'guild_application'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME = 'FK_E2BAE1E75E315342'
            ");

            if ($fkExists > 0) {
                $this->addSql('ALTER TABLE guild_application DROP FOREIGN KEY FK_E2BAE1E75E315342');
            }

            $indexes = $sm->listTableIndexes('guild_application');
            if (isset($indexes['idx_e2bae1e75e315342'])) {
                $this->addSql('DROP INDEX IDX_E2BAE1E75E315342 ON guild_application');
            }

            // Remettre NOT NULL (comme l'ancienne down) uniquement si la colonne existe
            $cols = $sm->listTableColumns('guild_application');
            if (isset($cols['personnage_id'])) {
                $this->addSql('ALTER TABLE guild_application CHANGE personnage_id personnage_id INT NOT NULL');
            }
        }

        if ($sm->tablesExist(['messenger_messages'])) {
            $mmIndexes = $sm->listTableIndexes('messenger_messages');

            if (isset($mmIndexes['idx_75ea56e0fb7336f0e3bd61ce16ba31dbbf396750'])) {
                $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
            }

            // On remet les indexes "simples" (comme l'ancienne down)
            $mmIndexes = $this->connection->createSchemaManager()->listTableIndexes('messenger_messages');

            if (!isset($mmIndexes['idx_75ea56e016ba31db'])) {
                $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
            }
            if (!isset($mmIndexes['idx_75ea56e0e3bd61ce'])) {
                $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
            }
            if (!isset($mmIndexes['idx_75ea56e0fb7336f0'])) {
                $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
            }
        }
    }
}
