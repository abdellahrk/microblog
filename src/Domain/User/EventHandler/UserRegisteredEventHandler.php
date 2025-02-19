<?php

namespace App\Domain\User\EventHandler;

use App\Domain\User\Event\UserRegistered;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEventListener]
final readonly class UserRegisteredEventHandler
{
    public function __construct(private MessageBusInterface $messageBus, private LoggerInterface $logger) {}

    public function __invoke(UserRegistered $event): void
    {
        $this->logger->info("new user registered: ".$event->getUserId());
    }
}