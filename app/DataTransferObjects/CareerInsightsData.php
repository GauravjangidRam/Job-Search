<?php

namespace App\DataTransferObjects;

class CareerInsightsData
{
    public function __construct(
        public readonly array $salaryData,
        public readonly array $hiringTrends,
        public readonly array $inDemandSkills,
    ) {}
}