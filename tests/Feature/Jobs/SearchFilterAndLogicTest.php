<?php

namespace Tests\Feature\Jobs;

use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 23: Search filters combine with AND logic
 *
 * For any combination of active filters (job_type, location_type, salary_range,
 * company_name), every JobListing in the results satisfies ALL active filter
 * conditions simultaneously.
 *
 * JobController::index() applies each filter conditionally via
 * if($request->filled(...)) with AND logic (sequential where clauses on the
 * same query builder).
 *
 * **Validates: Requirements 18.2, 18.5**
 */
class SearchFilterAndLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 100;

    /**
     * Possible job_type values used in test data.
     */
    private const JOB_TYPES = ['full-time', 'part-time', 'contract', 'internship'];

    /**
     * Possible location_type values used in test data.
     */
    private const LOCATION_TYPES = ['remote', 'hybrid', 'onsite'];

    /**
     * Company names used in test data.
     */
    private const COMPANY_NAMES = [
        'Acme Corp',
        'TechVision Inc',
        'Global Solutions',
        'DataDriven Labs',
        'CloudFirst Systems',
        'InnovateTech',
        'ByteForge',
        'QuantumLeap',
    ];

    /**
     * Seed the database with 25 active job listings with varied attributes.
     */
    private function seedListings(\Faker\Generator $faker): void
    {
        for ($i = 0; $i < 25; $i++) {
            $salaryMin = $faker->numberBetween(20000, 150000);
            $salaryMax = $salaryMin + $faker->numberBetween(5000, 50000);

            JobListing::create([
                'title' => $faker->jobTitle(),
                'company_name' => $faker->randomElement(self::COMPANY_NAMES),
                'location' => $faker->city(),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMax,
                'job_type' => $faker->randomElement(self::JOB_TYPES),
                'location_type' => $faker->randomElement(self::LOCATION_TYPES),
                'description' => $faker->paragraph(),
                'skills' => $faker->randomElements(['php', 'laravel', 'vue', 'react', 'sql', 'docker', 'python', 'java'], 3),
                'status' => 'active',
            ]);
        }
    }

    /**
     * Generate a random subset of filters for one iteration.
     *
     * @return array<string, mixed>
     */
    private function randomFilters(\Faker\Generator $faker): array
    {
        $filters = [];

        // Randomly decide which filters to activate (at least one)
        $possibleFilters = ['job_type', 'location_type', 'salary_min', 'salary_max', 'company_name'];
        $activeCount = $faker->numberBetween(1, count($possibleFilters));
        $activeFilters = $faker->randomElements($possibleFilters, $activeCount);

        if (in_array('job_type', $activeFilters)) {
            $filters['job_type'] = $faker->randomElement(self::JOB_TYPES);
        }

        if (in_array('location_type', $activeFilters)) {
            $filters['location_type'] = $faker->randomElement(self::LOCATION_TYPES);
        }

        if (in_array('salary_min', $activeFilters)) {
            $filters['salary_min'] = $faker->numberBetween(20000, 120000);
        }

        if (in_array('salary_max', $activeFilters)) {
            $filters['salary_max'] = $faker->numberBetween(50000, 200000);
        }

        if (in_array('company_name', $activeFilters)) {
            // Use a substring of a company name for partial match testing
            $fullName = $faker->randomElement(self::COMPANY_NAMES);
            $filters['company_name'] = substr($fullName, 0, $faker->numberBetween(3, strlen($fullName)));
        }

        return $filters;
    }

    /**
     * Check if a listing satisfies all active filter conditions.
     *
     * @param array<string, mixed> $filters
     */
    private function listingSatisfiesAllFilters(JobListing $listing, array $filters): bool
    {
        if (isset($filters['job_type']) && $listing->job_type !== $filters['job_type']) {
            return false;
        }

        if (isset($filters['location_type']) && $listing->location_type !== $filters['location_type']) {
            return false;
        }

        if (isset($filters['salary_min']) && $listing->salary_max < (int) $filters['salary_min']) {
            return false;
        }

        if (isset($filters['salary_max']) && $listing->salary_min > (int) $filters['salary_max']) {
            return false;
        }

        if (isset($filters['company_name'])) {
            if (stripos($listing->company_name, $filters['company_name']) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Property 23: Search filters combine with AND logic.
     *
     * For each iteration, randomly select a subset of filters and verify:
     * 1. Every listing in the results satisfies ALL active filter conditions.
     * 2. No listing that satisfies all conditions was excluded (completeness).
     *
     * **Validates: Requirements 18.2, 18.5**
     */
    public function test_search_filters_combine_with_and_logic(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250106);

        $this->seedListings($faker);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $filters = $this->randomFilters($faker);

            // Make the HTTP request with the selected filters
            $response = $this->get(route('jobs.index', $filters));
            $response->assertOk();

            $returnedJobs = $response->viewData('jobs');

            // Assert every returned listing satisfies ALL active filters
            foreach ($returnedJobs as $listing) {
                $this->assertTrue(
                    $this->listingSatisfiesAllFilters($listing, $filters),
                    sprintf(
                        'Iteration %d: Listing "%s" (id=%d, job_type=%s, location_type=%s, salary_min=%d, salary_max=%d, company=%s) does not satisfy all filters: %s',
                        $i,
                        $listing->title,
                        $listing->id,
                        $listing->job_type,
                        $listing->location_type,
                        $listing->salary_min,
                        $listing->salary_max,
                        $listing->company_name,
                        json_encode($filters)
                    )
                );
            }

            // Assert completeness: the total count reported by the paginator
            // matches the number of listings that satisfy all conditions, and
            // every returned ID belongs to the expected set.
            $allActive = JobListing::active()->get();
            $expectedIds = $allActive->filter(function ($listing) use ($filters) {
                return $this->listingSatisfiesAllFilters($listing, $filters);
            })->pluck('id')->sort()->values();

            $returnedIds = collect($returnedJobs->items())->pluck('id')->sort()->values();

            // The paginator's total must equal the full expected count
            $this->assertEquals(
                $expectedIds->count(),
                $returnedJobs->total(),
                sprintf(
                    'Iteration %d: Paginator total (%d) does not match expected count (%d). Filters: %s',
                    $i,
                    $returnedJobs->total(),
                    $expectedIds->count(),
                    json_encode($filters)
                )
            );

            // Every returned ID must be in the expected set (no false positives)
            foreach ($returnedIds as $id) {
                $this->assertTrue(
                    $expectedIds->contains($id),
                    sprintf(
                        'Iteration %d: Returned listing id=%d is not in the expected set. Filters: %s',
                        $i,
                        $id,
                        json_encode($filters)
                    )
                );
            }
        }
    }

    /**
     * Example: applying all filters simultaneously returns only listings
     * matching every condition.
     *
     * **Validates: Requirements 18.2, 18.5**
     */
    public function test_all_filters_applied_simultaneously(): void
    {
        // Create specific listings with known attributes
        JobListing::create([
            'title' => 'Senior PHP Developer',
            'company_name' => 'TechVision Inc',
            'location' => 'New York',
            'salary_min' => 80000,
            'salary_max' => 120000,
            'job_type' => 'full-time',
            'location_type' => 'remote',
            'description' => 'A great role.',
            'skills' => ['php', 'laravel'],
            'status' => 'active',
        ]);

        JobListing::create([
            'title' => 'Junior Designer',
            'company_name' => 'TechVision Inc',
            'location' => 'Boston',
            'salary_min' => 40000,
            'salary_max' => 60000,
            'job_type' => 'part-time',
            'location_type' => 'onsite',
            'description' => 'Design role.',
            'skills' => ['figma'],
            'status' => 'active',
        ]);

        JobListing::create([
            'title' => 'Backend Engineer',
            'company_name' => 'Acme Corp',
            'location' => 'Chicago',
            'salary_min' => 90000,
            'salary_max' => 130000,
            'job_type' => 'full-time',
            'location_type' => 'remote',
            'description' => 'Backend work.',
            'skills' => ['python'],
            'status' => 'active',
        ]);

        $filters = [
            'job_type' => 'full-time',
            'location_type' => 'remote',
            'salary_min' => 70000,
            'salary_max' => 125000,
            'company_name' => 'Tech',
        ];

        $response = $this->get(route('jobs.index', $filters));
        $response->assertOk();

        $jobs = $response->viewData('jobs');

        // Only the Senior PHP Developer at TechVision should match all filters
        $this->assertCount(1, $jobs);
        $this->assertEquals('Senior PHP Developer', $jobs->first()->title);
    }

    /**
     * Example: no filters returns all active listings.
     *
     * **Validates: Requirements 18.2**
     */
    public function test_no_filters_returns_all_active_listings(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(999);

        $this->seedListings($faker);

        $response = $this->get(route('jobs.index'));
        $response->assertOk();

        $jobs = $response->viewData('jobs');
        $totalActive = JobListing::active()->count();

        $this->assertEquals($totalActive, $jobs->total());
    }
}
