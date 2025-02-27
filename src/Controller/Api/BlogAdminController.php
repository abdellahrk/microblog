<?php

namespace App\Controller\Api;

use App\Domain\Shared\Command\DeleteFile;
use App\Dto\BlogPostDto;
use App\Dto\ObjectDtoInterface;
use App\Entity\BlogPost;
use App\Entity\User;
use App\Repository\BlogPostRepository;
use App\Service\PhotoUploadServiceInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Blog Post Admin', description: 'Every blog post administration endpoint')]
#[Route('/blog-admin')]
#[IsGranted("ROLE_USER")]
#[AsController]
final readonly class BlogAdminController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {

    }

    #[Route('/add', name: 'blog-admin-add', methods: ['POST'])]
    public function addBlogPost(
        #[MapRequestPayload] BlogPostDto $blogPostDto, 
        #[CurrentUser] User $user, 
        Request $request, 
        PhotoUploadServiceInterface $photoUploadService, 
        SluggerInterface $slugger,
    ): JsonResponse
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
                $blogPost->setImagePath($request->getSchemeAndHttpHost().'/api/blog_post/'.$fileName);
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

        return new JsonResponse(['blogPostId' => $blogPost->getId(), 'slug' => $blogPost->getSlug()], Response::HTTP_CREATED);
    }

    
    #[IsGranted('edit', 'blogPost')]
    #[Route('/edit/{id}', name: 'edit-blog-post', methods: ['PUT'], format: 'json')]
    public function updateBlogPost(
        BlogPost $blogPost,
        Request $request,
        ObjectDtoInterface $objectDto,
    ): JsonResponse
    {
        $payload = $request->getPayload();
        $data['title'] = $payload->get('title');
        $data['content'] = $payload->get('content');
        $objectDto->hydrate($data, $blogPost);

        return new JsonResponse(['blogPostId' => $blogPost->getId()], Response::HTTP_OK);
    }


    #[IsGranted('delete', 'blogPost')]
    #[Route('/delete/{id}', name: 'delete-blog-post', methods: ['DELETE'], format: 'json')]
    public function deleteBlogPost(BlogPost $blogPost, Request $request, #[Autowire('%kernel.project_dir%/public/')] $publicDir, BlogPostRepository $blogPostRepository): JsonResponse
    {
        $blogPost = $blogPostRepository->find($request->get('id'));
        if (!($blogPost instanceof BlogPost)) {
            return new JsonResponse(['error' => 'blog post not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->messageBus->dispatch(new DeleteFile($publicDir.'blog_post/'.$blogPost->getImage()));
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}