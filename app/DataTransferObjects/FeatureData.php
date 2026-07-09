<?php

namespace App\DataTransferObjects;

class FeatureData
{ 
    public function __construct(
        public readonly string $icon,
        public readonly string $title,
        public readonly string $description,
    ) {}
} 