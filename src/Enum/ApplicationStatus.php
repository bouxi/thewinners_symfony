<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statut d'une candidature.
 */
enum ApplicationStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
