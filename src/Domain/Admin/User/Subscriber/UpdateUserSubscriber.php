<?php

namespace App\Domain\Admin\User\Subscriber;

use App\Domain\Notification\Command\SendNotificationCommand;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class UpdateUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'updateUserAccount',
        ];
    }

    public function updateUserAccount(BeforeEntityUpdatedEvent $event): void
    {
        $user = $event->getEntityInstance();
        if (!($user instanceof User)) {
            return;
        }

        
        $unitOfWork = $this->entityManager->getUnitOfWork();
        
        if ($unitOfWork->getOriginalEntityData($user)['isActive'] !== $user->getIsActive()) {
            $user->getIsActive() === false ?
                $this->messageBus->dispatch(new SendNotificationCommand(NotificationType::ACCOUNT_DEACTIVATED->value, $user->getEmail())) :
                $this->messageBus->dispatch(new SendNotificationCommand(NotificationType::ACCOUNT_REACTIVATED->value, $user->getEmail()));
        }
    }
}