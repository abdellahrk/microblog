<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

interface EmailServiceInterface
{
    public function sendEmail(Email|TemplatedEmail $email, string $receiverEmail);
}