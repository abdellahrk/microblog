<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserDto
{
    public function __construct(
        #[Assert\Length(min: 3)]
        #[Assert\Email]
        public ?string $email,
        public ?string $username,
        public ?string $fullName,
    )
    {

    }
}