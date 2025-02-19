<?php

namespace App\Domain\Shared\Command;

final readonly class DeleteFile
{
    public function __construct(private string $path) {}

    public function getPath(): string
    {
        return $this->path;
    }


}