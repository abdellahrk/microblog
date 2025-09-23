<?php

namespace App\Controller\Api;

use App\Domain\User\Event\UserRegistered;
use App\Dto\UserDto;
use App\Entity\User;
use App\Shared\Persistence\PersistenceManagerInterface;
use Rami\ProblemDetailBundle\ProblemResponse\ProblemDetailsJsonResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use OpenApi\Attributes as OA;


#[OA\Tag(name: 'Authentication', description: 'User Authentication')]
#[AsController]
class AuthUserController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function registerUser(
        #[MapRequestPayload] UserDto $userDto,
        UserPasswordHasherInterface $passwordHasher,
        EventDispatcherInterface $eventDispatcher,
        SluggerInterface $slugger,
        PersistenceManagerInterface $persistenceManager,
    ): Response
    {
        $user = new User();

        $existingUser = $persistenceManager->getRepository(User::class)->findOneBy(['email' => $userDto->email]);

        if ($existingUser instanceof User) {
            return new ProblemDetailsJsonResponse(Response::HTTP_CONFLICT, title: 'Email is taken', detail: sprintf('%s email is taken', $userDto->email));
        }
        $user->setEmail($userDto->email)
            ->setUsername($userDto->username)
            ->setPassword($passwordHasher->hashPassword($user, $userDto->password))
            ->setFullName($userDto->fullName)
            ->setRoles(['ROLE_USER', 'ROLE_AUTHOR'])
            ->setSlug(strtolower($slugger->slug($user->getFullName())));

        try {
            $persistenceManager->persistAndFlush($user);
        } catch (\Exception $e) {
            return new ProblemDetailsJsonResponse(Response::HTTP_BAD_REQUEST, title: 'Bad Request', detail: sprintf('%s', $e->getMessage()));
        }

        $eventDispatcher->dispatch(new UserRegistered($user->getId()));

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}