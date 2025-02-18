<?php

namespace App\Dto;

use Doctrine\ORM\EntityManagerInterface;

final readonly class ObjectDto implements ObjectDtoInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function hydrate(array $data, object $object): object
    {
        foreach ($data as $classProperty => $value) {
            if (property_exists($object, $classProperty)) {
                $method = 'set' . ucfirst($classProperty);
                $object->$method($value);
            }
        }
        $this->entityManager->flush();
        return $object;
    }
}