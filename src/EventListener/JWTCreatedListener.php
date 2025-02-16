<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener('lexik_jwt_authentication.on_authentication_success', 'onJWTCreated', 100)]
readonly class JWTCreatedListener
{
    public function __construct(
        private RequestStack $requestStack,
    )
    {}
    public function onJWTCreated(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $payload = $event->getData();
        $user = $event->getUser();
        if (!($user instanceof User)) {
            return;
        }
        if ($user->getEmail() !== null) {
            $payload['user']['email'] = $user->getEmail();
        }
        $payload['user']['username'] = $user->getUsername();
        $payload['user']['fullName'] = $user->getFullName();
        $payload['user']['id'] = $user->getId();

        $event->setData($payload);
    }
}