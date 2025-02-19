<?php

namespace App\Controller\Api;

use App\Entity\BlogPost;
use App\Repository\BlogPostRepository;
use App\Service\CustomSerialiserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/all-posts', name: 'blog.posts.index', methods: ['GET'])]
    public function getBlogPosts(BlogPostRepository $blogPostRepository): JsonResponse
    {
        $blogPostsData = $blogPostRepository->findBlogPostsPaginated(1,6);
        $blogPosts = $this->customSerialiser->serialise($blogPostsData["data"], ['blog_posts']);
        $blogPostsData["data"] = json_decode($blogPosts);
        return  new JsonResponse($blogPostsData, Response::HTTP_OK, []);
    }

    #[Route('/{slug}', name: 'blog.show', methods: ['GET'])]
    public function getBlogPost( #[MapEntity(mapping: ['slug' => 'slug'])] BlogPost $blogPost, Request $request, LoggerInterface $logger): JsonResponse
    {
        $logger->warning("base url: ", [$request->getSchemeAndHttpHost()]);
        $blog = $this->customSerialiser->serialise($blogPost, ['blog_post']);
        return new JsonResponse($blog, Response::HTTP_OK, [], true);
    }
}