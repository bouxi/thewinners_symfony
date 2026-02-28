<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260122114553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Safe drops/alterations: avoid failing on fresh DB (recipes/users tables, guild_application/user columns).';
    }

    public function up(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        // ✅ Ne pas planter sur DB neuve : drop only if exists
        if ($sm->tablesExist(['recipes'])) {
            $this->addSql('DROP TABLE recipes');
        }

        if ($sm->tablesExist(['users'])) {
            $this->addSql('DROP TABLE users');
        }

        // ✅ Ajouter player_name seulement si la table existe ET si la colonne n'existe pas déjà
        if ($sm->tablesExist(['guild_application'])) {
            $columns = $sm->listTableColumns('guild_application');
            if (!isset($columns['player_name'])) {
                $this->addSql('ALTER TABLE guild_application ADD player_name VARCHAR(50) NOT NULL');
            }
        }

        // ✅ Ajouter combat_role seulement si la table user existe ET si la colonne n'existe pas déjà
        if ($sm->tablesExist(['user'])) {
            $columns = $sm->listTableColumns('user');
            if (!isset($columns['combat_role'])) {
                $this->addSql('ALTER TABLE user ADD combat_role VARCHAR(255) DEFAULT NULL');
            }
        }
    }

    public function down(Schema $schema): void
    {
        $sm = $this->connection->createSchemaManager();

        // ⚠️ DOWN : recrée uniquement si ça n'existe pas déjà
        if (!$sm->tablesExist(['recipes'])) {
            $this->addSql(
                "CREATE TABLE recipes (
                    recipe_id INT AUTO_INCREMENT NOT NULL,
                    title VARCHAR(128) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`,
                    recipe TEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`,
                    author VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`,
                    is_enabled TINYINT NOT NULL,
                    PRIMARY KEY (recipe_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = ''"
            );
        }

        if (!$sm->tablesExist(['users'])) {
            $this->addSql(
                "CREATE TABLE users (
                    user_id INT AUTO_INCREMENT NOT NULL,
                    full_name VARCHAR(64) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`,
                    email VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`,
                    password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`,
                    age INT NOT NULL,
                    PRIMARY KEY (user_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = ''"
            );
        }

        // ✅ Retirer player_name seulement si la table existe ET si la colonne existe
        if ($sm->tablesExist(['guild_application'])) {
            $columns = $sm->listTableColumns('guild_application');
            if (isset($columns['player_name'])) {
                $this->addSql('ALTER TABLE guild_application DROP player_name');
            }
        }

        // ✅ Retirer combat_role seulement si la table user existe ET si la colonne existe
        if ($sm->tablesExist(['user'])) {
            $columns = $sm->listTableColumns('user');
            if (isset($columns['combat_role'])) {
                $this->addSql('ALTER TABLE `user` DROP combat_role');
            }
        }
    }
}
