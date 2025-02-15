<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class HomeController
{
    #[Route('/', name: 'home')]
    public function index(): JsonResponse
    {
        return new JsonResponse(['works' => 'works']);
    }
}