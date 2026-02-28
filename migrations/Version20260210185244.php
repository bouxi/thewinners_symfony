<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210185244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add personnage_id to guild_application safely (immediate DDL before validation), and adjust messenger_messages indexes safely.';
    }

    public function up(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        /**
         * --------------------------------------------------------------------
         * 1) guild_application.personnage_id + index + FK (SAFE + immediate)
         * --------------------------------------------------------------------
         */
        if ($sm->tablesExist(['guild_application'])) {
            $columns = $sm->listTableColumns('guild_application');

            // ✅ (A) Ajouter la colonne si elle n'existe pas (EXECUTE IMMEDIATELY)
            if (!isset($columns['personnage_id'])) {
                // immédiat, sinon la requête de validation plus bas casse
                $this->connection->executeStatement(
                    'ALTER TABLE guild_application ADD personnage_id INT DEFAULT NULL'
                );

                // refresh schema info
                $sm = $this->connection->createSchemaManager();
            }

            // ✅ (B) Index (on peut faire addSql, pas besoin de SELECT ensuite)
            $indexes = $sm->listTableIndexes('guild_application');
            if (!isset($indexes['idx_e2bae1e75e315342'])) {
                $this->addSql('CREATE INDEX IDX_E2BAE1E75E315342 ON guild_application (personnage_id)');
            }

            // ✅ (C) Vérifie les données (maintenant la colonne existe forcément)
            $invalidCount = (int) $this->connection->fetchOne("
                SELECT COUNT(*)
                FROM guild_application ga
                LEFT JOIN personnage p ON p.id = ga.personnage_id
                WHERE ga.personnage_id IS NOT NULL
                  AND p.id IS NULL
            ");

            if ($invalidCount > 0) {
                throw new \RuntimeException(sprintf(
                    "Impossible d'ajouter la FK: %d candidature(s) ont un personnage_id invalide. Corrige les données puis relance.",
                    $invalidCount
                ));
            }

            // ✅ (D) FK uniquement si absente
            $fkExists = (int) $this->connection->fetchOne("
                SELECT COUNT(*)
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE CONSTRAINT_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'guild_application'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND CONSTRAINT_NAME = 'FK_E2BAE1E75E315342'
            ");

            if ($fkExists === 0) {
                $this->addSql(
                    'ALTER TABLE guild_application
                     ADD CONSTRAINT FK_E2BAE1E75E315342
                     FOREIGN KEY (personnage_id) REFERENCES personnage (id) ON DELETE RESTRICT'
                );
            }
        }

        /**
         * --------------------------------------------------------------------
         * 2) messenger_messages indexes (safe)
         * --------------------------------------------------------------------
         */
        if ($sm->tablesExist(['messenger_messages'])) {
            $indexes = $sm->listTableIndexes('messenger_messages');

            if (isset($indexes['idx_75ea56e016ba31db'])) {
                $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
            }
            if (isset($indexes['idx_75ea56e0e3bd61ce'])) {
                $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
            }
            if (isset($indexes['idx_75ea56e0fb7336f0'])) {
                $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
            }

            // Re-fetch indexes (pas obligatoire, mais propre)
            $sm2 = $this->connection->createSchemaManager();
            $indexes2 = $sm2->listTableIndexes('messenger_messages');

            if (!isset($indexes2['idx_75ea56e0fb7336f0e3bd61ce16ba31dbbf396750'])) {
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

        // Revert messenger_messages indexes
        if ($sm->tablesExist(['messenger_messages'])) {
            $indexes = $sm->listTableIndexes('messenger_messages');

            if (isset($indexes['idx_75ea56e0fb7336f0e3bd61ce16ba31dbbf396750'])) {
                $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
            }

            $sm2 = $this->connection->createSchemaManager();
            $indexes2 = $sm2->listTableIndexes('messenger_messages');

            if (!isset($indexes2['idx_75ea56e016ba31db'])) {
                $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
            }
            if (!isset($indexes2['idx_75ea56e0e3bd61ce'])) {
                $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
            }
            if (!isset($indexes2['idx_75ea56e0fb7336f0'])) {
                $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
            }
        }

        // Revert guild_application personnage_id
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

            $columns = $sm->listTableColumns('guild_application');
            if (isset($columns['personnage_id'])) {
                $this->addSql('ALTER TABLE guild_application DROP personnage_id');
            }
        }
    }
}
