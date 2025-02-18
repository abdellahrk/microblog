<?php

namespace App\Controller\Api;

use App\Entity\BlogPost;
use App\Service\CustomSerialiserInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/blog')]
final readonly class BlogController
{
    public function __construct(
        private CustomSerialiserInterface $customSerialiser,
    )
    {
    }

    #[Route('/{slug}', name: 'blog.show', methods: ['GET'])]
    public function getBlogPost( #[MapEntity(mapping: ['slug' => 'slug'])] BlogPost $blogPost): JsonResponse
    {
        $blog = $this->customSerialiser->serialise($blogPost, ['blog_post']);
        return new JsonResponse($blog, Response::HTTP_OK, [], true);
    }
}