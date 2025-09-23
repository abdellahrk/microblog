<?php

namespace App\Shared\Persistence;

use Doctrine\Persistence\ObjectRepository;

interface PersistenceManagerInterface
{
    public function getRepository(string $class): ObjectRepository;

    public function flush(): void;

    public function remover(object $entity): void;

    public function persist(object $entity): void;

    public function persistAndFlush(object $entity): void;
}