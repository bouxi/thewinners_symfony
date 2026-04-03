<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\GuideNavigationProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension Twig qui expose les données de navigation des guides.
 */
final class GuideNavigationExtension extends AbstractExtension
{
    public function __construct(
        private readonly GuideNavigationProvider $guideNavigationProvider,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('guide_root_categories', [$this, 'getGuideRootCategories']),
        ];
    }

    public function getGuideRootCategories(): array
    {
        return $this->guideNavigationProvider->getRootCategories();
    }
}