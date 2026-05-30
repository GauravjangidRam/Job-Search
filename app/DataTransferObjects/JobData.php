<?php

namespace App\DataTransferObjects;

class JobData
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $company,
        public readonly ?string $logoUrl,
        public readonly string $salaryMin,
        public readonly string $salaryMax,
        public readonly string $location,
        public readonly array $tags,
        public readonly int $applicantCount,
        public readonly bool $isTrending,
        public readonly string $jobType,
        public readonly string $locationType,
        public readonly string $salaryRange,
        public readonly string $postedDate,
    ) {}
}
