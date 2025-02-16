<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PhotoUploadService implements PhotoUploadServiceInterface
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/')] private $publicDir,
        private LoggerInterface $logger
    ) {
    }

    public function upload(UploadedFile $uploadedFile, string $uploadDirectory): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_BASENAME);

        try {
            $uploadedFile->move($this->publicDir . $uploadDirectory, $originalFilename);

        } catch (FileException $e) {
            $this->logger->error("Error is :", (array) $e->getMessage());
            throw new FileException($e->getMessage());
        }

        return $originalFilename;
    }

}