<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

interface NotificationServiceInterface
{
    public function sendEmailNotification(Email|TemplatedEmail $email, string $receiverEmail):void;
}