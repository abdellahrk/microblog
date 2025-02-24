<?php

namespace App\Domain\User\EventHandler;

use App\Domain\Notification\Command\SendNotificationCommand;
use App\Domain\User\Event\UserRegistered;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener]
final readonly class UserRegisteredEventHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private UserRepository $userRepository,
    ) {}

    public function __invoke(UserRegistered $event): void
    {
        $this->logger->info("Event: UserRegistered");
        $user = $this->userRepository->find($event->getUserId());

        if (!($user instanceof User)) {
            return;
        }

        $this->messageBus->dispatch(new SendNotificationCommand(NotificationType::WELCOME_EMAIL->value, $user->getEmail()));
    }
}