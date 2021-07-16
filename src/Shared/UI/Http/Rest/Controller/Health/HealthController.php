<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\Rest\Controller\Health;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HealthController extends AbstractController
{
    /**
     * @Route(
     *     "/health",
     *     name="health-check"
     * )
     */
    public function __invoke(): Response
    {
        return JsonResponse::create();
    }
}
