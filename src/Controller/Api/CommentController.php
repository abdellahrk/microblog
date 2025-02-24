<?php

namespace App\Controller\Api;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Comments', description: 'Comments endpoints')]
#[AsController]
final readonly class CommentController
{
    #[Route(path: '/add-comment', name: 'add_comment', methods: ['POST'])]
    public function addComment(Request $request, #[CurrentUser] User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $payload = $request->getPayload();
        $blogPost = $entityManager->find(BlogPost::class, $payload->get('blog_post_id'));

        if (!$blogPost instanceof BlogPost) {
            return new JsonResponse(['message' => 'Blog post not found'], Response::HTTP_NOT_FOUND);
        }

        $comment = (new Comment())
            ->setAuthor($user)
            ->setCommentText($payload->get('comment'))
            ->setBlogPost($blogPost)
        ;

        $entityManager->persist($comment);
        $entityManager->flush();

        return new JsonResponse(['success' => true], Response::HTTP_CREATED);
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route(path: '/remove-comment/{id}', name: 'delete_comment', methods: ['DELETE'])]
    public function removeComment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $comment = $entityManager->find(Comment::class, $request->getPayload()->get('id'));
            $entityManager->remove($comment);
            $entityManager->flush();
        } catch (OptimisticLockException|ORMException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }
        return new JsonResponse(['success' => true], Response::HTTP_NO_CONTENT);
    }
}