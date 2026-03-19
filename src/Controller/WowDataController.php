<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\WowData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class WowDataController extends AbstractController
{
    #[Route('/api/wow/specs', name: 'api_wow_specs', methods: ['GET'])]
    public function specs(Request $request): JsonResponse
    {
        $className = (string) $request->query->get('class', '');

        if ($className === '' || !isset(WowData::CLASSES[$className])) {
            return $this->json([
                'class' => $className,
                'specs' => [],
            ]);
        }

        return $this->json([
            'class' => $className,
            'specs' => WowData::CLASSES[$className],
        ]);
    }
}