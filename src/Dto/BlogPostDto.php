<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class BlogPostDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $title,
        #[Assert\NotBlank]
        public string $content,
    ) {}
}