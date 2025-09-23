<?php

namespace App\Shared\Persistence;

use App\Shared\Persistence\PersistenceManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

final class PersistenceManager implements PersistenceManagerInterface
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    private ObjectManager $objectManager {
        get => $this->registry->getManager();
    }

    public function getRepository(string $class): ObjectRepository
    {
        return $this->objectManager->getRepository($class);
    }

    public function flush(): void
    {
        $this->objectManager->flush();
    }

    public function remover(object $entity): void
    {
        $this->objectManager->remove($entity);
        $this->objectManager->flush();
    }

    public function persist(object $entity): void
    {
        $this->objectManager->persist($entity);
    }

    public function persistAndFlush(object $entity): void
    {
       $this->objectManager->persist($entity);
       $this->objectManager->flush();
    }
}