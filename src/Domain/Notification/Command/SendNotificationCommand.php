<?php

namespace App\Domain\Notification\Command;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final readonly class SendNotificationCommand
{
    public function __construct(
        private string $type,
        private string $receiverEmail
    ) {}

    public function getType(): string
    {
        return $this->type;
    }

    public function getReceiverEmail(): string
    {
        return $this->receiverEmail;
    }
}