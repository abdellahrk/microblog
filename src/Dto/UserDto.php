<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3)]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        public string $username,
        #[Assert\NotBlank]
        public string $password,
        #[Assert\NotBlank]
        public string $fullName,
    )
    {

    }
}