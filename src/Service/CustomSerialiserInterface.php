<?php

namespace App\Service;

interface CustomSerialiserInterface
{
    public function serialise(object|array $data, array $groups = []): string;
}