<?php

namespace App\DataTransferObjects;

class TestimonialData
{
    public function __construct(
        public readonly string $name,
        public readonly string $role,
        public readonly string $avatarUrl,
        public readonly string $text,
        public readonly int $rating,
    ) {}
}
