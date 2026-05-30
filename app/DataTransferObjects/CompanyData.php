<?php

namespace App\DataTransferObjects;

class CompanyData
{
    public function __construct(
        public readonly string $name,
        public readonly string $logoUrl,
        public readonly string $culture,
        public readonly array $metrics,
        public readonly array $perks,
        public readonly bool $isHiring,
    ) {}
}
