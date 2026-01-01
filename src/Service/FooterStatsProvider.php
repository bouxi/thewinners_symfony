<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use App\Repository\RaidEventRepository;

final class FooterStatsProvider
{
    public function __construct(
        private UserRepository $userRepository,
        private RaidEventRepository $raidEventRepository
    ) {}

    public function getStats(): array
    {
        return [
            'members' => $this->userRepository->count([]),
            'upcoming_raids' => $this->raidEventRepository->countUpcoming(),
        ];
    }

}
