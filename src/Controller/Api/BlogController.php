<?php

namespace App\Controller\Api;

use App\Entity\BlogPost;
use App\Entity\User;
use App\Repository\BlogPostRepository;
use App\Repository\UserRepository;
use App\Service\CustomSerialiserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;


#[OA\Tag(name: 'BlogPost', description: 'Blog post')]
#[AsController]
#[Route('/blog')]
final readonly class BlogController
{
    public function __construct(
        private CustomSerialiserInterface $customSerialiser,
    )
    {
    }

    #[Route('/all', name: 'blog.posts.index', methods: ['GET'])]
    public function getBlogPosts(BlogPostRepository $blogPostRepository, Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $nbPerPage = $request->query->getInt('nbPerPage', 10);
        $blogPostsData = $blogPostRepository->findBlogPostsPaginated($page,$nbPerPage);
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

    #[Route('/by/{slug}', name: 'blog.posts.by.user', methods: ['GET'])]
    public function getUserBlogPosts(UserRepository $userRepository, Request $request, BlogPostRepository $blogPostRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $nbPerPage = $request->query->getInt('nbPerPage', 30);

        $user = $userRepository->findOneBy(['slug' => $request->get('slug')]);

        if (!($user instanceof User)) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $blogPosts = $blogPostRepository->getBlogPostsByAuthor($user, $page, $nbPerPage);
        $serializedBlogPosts = $this->customSerialiser->serialise($blogPosts, ['blog_posts']);

        return new JsonResponse($serializedBlogPosts, Response::HTTP_OK, [], true);
    }
}