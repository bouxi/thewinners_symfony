<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260320115015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Corrige les dates de consentement manquantes dans user_consent';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        // Si privacy_accepted = 1 mais privacy_accepted_at est NULL,
        // on reprend une date existante si possible, sinon NOW().
        $this->addSql("
            UPDATE user_consent
            SET privacy_accepted_at = COALESCE(terms_accepted_at, cookies_accepted_at, NOW())
            WHERE privacy_accepted = 1
              AND privacy_accepted_at IS NULL
        ");

        // Si terms_accepted = 1 mais terms_accepted_at est NULL,
        // on reprend une date existante si possible, sinon NOW().
        $this->addSql("
            UPDATE user_consent
            SET terms_accepted_at = COALESCE(privacy_accepted_at, cookies_accepted_at, NOW())
            WHERE terms_accepted = 1
              AND terms_accepted_at IS NULL
        ");

        // Si cookies_accepted = 1 mais cookies_accepted_at est NULL,
        // on reprend une date existante si possible, sinon NOW().
        $this->addSql("
            UPDATE user_consent
            SET cookies_accepted_at = COALESCE(privacy_accepted_at, terms_accepted_at, NOW())
            WHERE cookies_accepted = 1
              AND cookies_accepted_at IS NULL
        ");
    }

    public function down(Schema $schema): void
    {
        // Pas de rollback fiable sur des dates reconstruites.
    }
}