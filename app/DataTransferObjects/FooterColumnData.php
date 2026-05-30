<?php

namespace App\DataTransferObjects;

class FooterColumnData
{
    public function __construct(
        public readonly string $heading,
        public readonly array $links,
    ) {}
}
