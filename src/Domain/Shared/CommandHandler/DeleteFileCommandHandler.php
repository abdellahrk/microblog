<?php

namespace App\Domain\Shared\CommandHandler;

use App\Domain\Shared\Command\DeleteFile;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DeleteFileCommandHandler
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(DeleteFile $command): void
    {
        $filesystem = new Filesystem();
        try {
            $filesystem->remove($command->getPath());
        } catch (IOExceptionInterface|FileException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}