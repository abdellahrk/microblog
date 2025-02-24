<?php

namespace App\Controller\Api;

use App\Domain\User\Event\UserRegistered;
use App\Dto\UserDto;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;


#[OA\Tag(name: 'Authentication', description: 'User Authentication')]
#[AsController]
class AuthUserController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function registerUser(
        Request $request,
        EntityManagerInterface $entityManager,
        #[MapRequestPayload] UserDto $userDto,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        EventDispatcherInterface $eventDispatcher,
        SluggerInterface $slugger
    ): JsonResponse
    {
        $user = new User();
        $user->setEmail($userDto->email)
            ->setUsername($userDto->username)
            ->setPassword($passwordHasher->hashPassword($user, $userDto->password))
            ->setFullName($userDto->fullName)
            ->setRoles(['ROLE_USER', 'ROLE_AUTHOR'])
            ->setSlug(strtolower($slugger->slug($user->getFullName())))
        ;

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException|\Exception $e) {
            return new JsonResponse(['error: '.$e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $eventDispatcher->dispatch(new UserRegistered($user->getId()));

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}