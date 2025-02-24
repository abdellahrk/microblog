<?php

namespace App\Service;

use App\Service\NotificationServiceInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

final readonly class NotificationService implements NotificationServiceInterface
{

    public function __construct(private EmailServiceInterface $emailService) {}

    public function sendEmailNotification(TemplatedEmail|Email $email, string $receiverEmail): void
    {
        $this->emailService->sendEmail($email, $receiverEmail);
    }
}