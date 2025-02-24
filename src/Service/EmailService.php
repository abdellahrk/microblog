<?php

namespace App\Service;

use App\Service\EmailServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final readonly class EmailService implements EmailServiceInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private ParameterBagInterface $parameterBag,
    ) {}

    public function sendEmail(TemplatedEmail|Email $email, string $receiverEmail): void
    {
        $email
            ->from(new Address($this->parameterBag->get('app.email_email_sender'), $this->parameterBag->get('app.name')))
            ->to($receiverEmail);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface|\Exception $exception) {
            $this->logger->error("cannot send email: ".$exception->getMessage());
        }
    }
}