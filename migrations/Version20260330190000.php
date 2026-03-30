<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill user_consent for existing users without consent row';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO user_consent (
                user_id,
                privacy_accepted,
                terms_accepted,
                cookies_accepted,
                terms_accepted_at,
                privacy_accepted_at,
                cookies_accepted_at,
                cookie_choice,
                privacy_version,
                terms_version,
                cookies_version
            )
            SELECT
                u.id,
                1,
                1,
                0,
                COALESCE(u.date_inscription, NOW()),
                COALESCE(u.date_inscription, NOW()),
                NULL,
                NULL,
                '1.0',
                '1.0',
                NULL
            FROM user u
            LEFT JOIN user_consent uc ON uc.user_id = u.id
            WHERE uc.id IS NULL
        ");
    }

    public function down(Schema $schema): void
    {
        // Pas de rollback fiable ici.
    }
}