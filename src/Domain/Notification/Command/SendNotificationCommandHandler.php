<?php

namespace App\Domain\Notification\Command;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\NotificationServiceInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[AsMessageHandler]
final readonly class SendNotificationCommandHandler
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private UserRepository $userRepository,
        private NotificationServiceInterface $notificationService,
    ) {}

    public function __invoke(SendNotificationCommand $command): void
    {
        $notification = $this->notificationRepository->findOneBy(['type' => $command->getType()]);

        if (!($notification instanceof Notification)) {
            return;
        }

        $user = $this->userRepository->findOneBy(['email' => $command->getReceiverEmail()]);

        $email = new TemplatedEmail();
        $email->subject($notification->getSubject())
            ->htmlTemplate('notifications/email_notification.html.twig')
        ;

        $loader = new FilesystemLoader();
        $twig = new Environment($loader);

        $emailContent = $twig->createTemplate($notification->getContent());

        $email->context([
            'content' => $emailContent->render([
                'username' => $user->getUsername() ?? null,
                'fullName' => $user->getFullName() ?? null,
                'email' => $user->getEmail() ?? null,
            ]),
        ]);

        $this->notificationService->sendEmailNotification($email, $user->getEmail());
    }
}