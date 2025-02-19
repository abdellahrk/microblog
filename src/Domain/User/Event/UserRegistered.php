<?php

namespace App\Domain\User\Event;

final readonly class UserRegistered
{
    public function __construct(
        private int $userId,
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }
}