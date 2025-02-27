<?php

namespace App\Controller\Api;

use App\Dto\ObjectDtoInterface;
use App\Dto\UpdateUserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'User Endpoints', description: 'User endpoints')]
#[IsGranted("ROLE_USER")]
#[AsController]
class UserController
{
    public function __construct() {}

    #[OA\Put(
        path: '/user/{slug}',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property('email', 'email', required: ['false'])
                ],
            )
        )
    )]
    #[Route('/user/{slug}', name: 'my_profile', methods: ['PUT'])]
    public function updateProfile(#[CurrentUser] User $user, Request $request, ObjectDtoInterface $objectDto,
    #[MapRequestPayload] UpdateUserDto $updateUserDto,
    ): JsonResponse
    {
        $payload = $request->getPayload();

        $objectDto->hydrate((array)$updateUserDto, $user);

        if (null !== $payload->get('email') || null !== $payload->get('username')) {
            return new JsonResponse(['success' => true, 'message' => 'You\'ll need to log back in'], Response::HTTP_PERMANENTLY_REDIRECT);
        }

        return new JsonResponse(['success' => true, 'user' => $user->getSlug(), Response::HTTP_OK]);
    }

    #[Route('/user/update-password', name: 'app_api_user_update_password', methods: ['PUT'])]
    public function updatePassword(#[CurrentUser] User $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        $password = $request->getPayload()->get('password');

        if (null === $password) {
            return new JsonResponse(['success' => false, 'message' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }

        $passwordHasher->hashPassword($user, $password);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Your password has been updated'], Response::HTTP_OK);
    }
}