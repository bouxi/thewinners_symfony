<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\FooterStatsProvider;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class FooterStatsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private FooterStatsProvider $provider) {}

    public function getGlobals(): array
    {
        return [
            'footer_stats' => $this->provider->getStats(),
        ];
    }
}
