<?php

namespace App\Controller\Api;

use App\Dto\BlogPostDto;
use App\Dto\ObjectDtoInterface;
use App\Entity\BlogPost;
use App\Entity\User;
use App\Service\PhotoUploadServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog')]
#[AsController]
final readonly class BlogController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {

    }
    #[IsGranted("ROLE_USER")]
    #[Route('/add')]
    public function addBlogPost(#[MapRequestPayload] BlogPostDto $blogPostDto, #[CurrentUser] User $user, Request $request, PhotoUploadServiceInterface $photoUploadService, SluggerInterface $slugger): JsonResponse
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle($blogPostDto->title)
            ->setContent($blogPostDto->content)
            ->setAuthor($user)
            ->setSlug(strtolower($slugger->slug($blogPostDto->title)))
        ;

        if (count($request->files) > 0) {
            $file = $request->files->get("image");
            try {
                $fileName = $photoUploadService->upload($file, 'blog_post/');
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

    #[IsGranted("ROLE_USER")]
    #[Route('/edit/{id}', name: 'edit-blog-post', methods: ['PUT'])]
    public function updateBlogPost(BlogPost $blogPost, Request $request, ObjectDtoInterface $objectDto, PhotoUploadServiceInterface $photoUploadService): JsonResponse
    {
        if (count($request->files) > 0) {
            $file = $request->files->get("image");
            $filesystem = new Filesystem();
            $filesystem->remove($filesystem->exists('blog_post/'.$blogPost->getImage()));
            $photoUploadService->upload($file, 'blog_post/');
        }

        $objectDto->hydrate($request->getPayload()->all(), $blogPost);
        return new JsonResponse(['blogPostId' => $blogPost->getId()], Response::HTTP_OK);
    }
}