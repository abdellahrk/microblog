<?php

namespace App\Controller\Api;

use App\Dto\BlogPostDto;
use App\Entity\BlogPost;
use App\Entity\User;
use App\Service\PhotoUploadServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class BlogController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {

    }
    #[IsGranted("ROLE_USER")]
    public function addBlogPost(BlogPostDto $blogPostDto, #[CurrentUser] User $user, Request $request, PhotoUploadServiceInterface $photoUploadService): JsonResponse
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle($blogPostDto->title)
            ->setContent($blogPostDto->content)
            ->setAuthor($user)
        ;

        if (count($request->files) > 0) {
            $file = $request->files[0];
            try {
                $fileName = $photoUploadService->upload($file, 'blog_post/'.$blogPost->getId());
                $blogPost->setImage($fileName);
            } catch (FileException $e) {
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $this->entityManager->persist($blogPost);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException|\Exception $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['blogPostId' => $blogPost->getId()], Response::HTTP_CREATED);
    }
}