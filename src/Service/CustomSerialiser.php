<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CustomSerialiser implements CustomSerialiserInterface
{
    private Serializer $serializer;

    public function __construct(
    )
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());

        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);
        $dateCallback = function (object $attributeValue, object $object, string $attributeName, ?string $format = null, array $context = []): string {
            return $attributeValue instanceof \DateTimeImmutable ? $attributeValue->format(\DateTime::W3C) : '';
        };
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (object $object, ?string $format, array $context): string {
                return $object->getId();
            },
            AbstractNormalizer::CALLBACKS => [
                'createdAt' => $dateCallback,
                'updatedAt' => $dateCallback,
                'commentedAt' => $dateCallback,
            ]
        ];
        $this->serializer = new Serializer([new ObjectNormalizer($classMetadataFactory,null,null,null,$discriminator,null,$defaultContext)], [new JsonEncoder()]);
    }


    public function serialise(object|array $data, array $groups = []): string
    {
        return $this->serializer->serialize($data, 'json', ['groups' => $groups]);
    }
}