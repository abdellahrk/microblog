<?php

namespace App\Dto;

interface ObjectDtoInterface
{
    public function hydrate(array $data, object $object): object;
}