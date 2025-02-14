<?php

namespace App\Controller;

use App\Dto\UserDto;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
class RegisterUserController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function registerUser(Request $request, EntityManagerInterface $entityManager, #[MapRequestPayload] UserDto $userDto, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator,  LoggerInterface $logger): JsonResponse
    {
        $errorsBag = $validator->validate($userDto);

        if (count($errorsBag) > 0) {
            $errors = [];
            foreach ($errorsBag as $error) {
                $errors[$error->getCause()] = $error->getMessage();
            }

            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
        }

        $logger->warning("payload",[$userDto]);

        $user = new User();
        $user->setEmail($userDto->email)
            ->setUsername($userDto->username)
            ->setPassword($passwordHasher->hashPassword($user, $userDto->password))
            ->setFullName($userDto->fullName)
            ->setRoles(['ROLE_USER', 'ROLE_AUTHOR'])
        ;

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['email or username already exist'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}