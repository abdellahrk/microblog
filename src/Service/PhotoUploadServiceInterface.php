<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface PhotoUploadServiceInterface
{
    public function upload(UploadedFile $uploadedFile, string $uploadDirectory): string;
}