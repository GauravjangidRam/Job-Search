<?php

namespace Tests\Feature\Jobs;

use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 22: Only active listings visible in public search
 *
 * For any public job search or browse query, all returned JobListings have
 * status "active". No listing with status "draft" or "closed" appears in the
 * results.
 *
 * The test replicates the controller's query logic (JobListing::active() with
 * optional filters) at the DB/query level, verifying the property across 120
 * randomized filter combinations against a dataset containing listings of all
 * statuses.
 *
 * **Validates: Requirements 15.2, 15.5**
 */
class ActiveListingsOnlyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations (design requires >= 100).
     */
    private const ITERATIONS = 120;

    /**
     * Seed listings with varied statuses and attributes.
     *
     * @return void
     */
    private function seedListings(\Faker\Generator $faker, int $count = 30): void
    {
        $statuses = ['draft', 'active', 'closed'];
        $jobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];
        $locationTypes = ['Remote', 'On-site', 'Hybrid'];
        $companies = [];

        // Pre-generate a small pool of company names so filters can match
        for ($c = 0; $c < 5; $c++) {
            $companies[] = $faker->company();
        }

        for ($i = 0; $i < $count; $i++) {
            $min = $faker->numberBetween(30000, 120000);
            $max = $min + $faker->numberBetween(5000, 80000);

            JobListing::create([
                'title' => $faker->jobTitle(),
                'company_name' => $faker->randomElement($companies),
                'location' => $faker->city(),
                'salary_min' => $min,
                'salary_max' => $max,
                'job_type' => $faker->randomElement($jobTypes),
                'location_type' => $faker->randomElement($locationTypes),
                'description' => $faker->paragraph(),
                'skills' => $faker->randomElements(['PHP', 'Laravel', 'Vue', 'React', 'SQL', 'AWS', 'Docker', 'Python', 'Node.js'], $faker->numberBetween(1, 4)),
                'status' => $faker->randomElement($statuses),
            ]);
        }
    }

    /**
     * Build a random set of filter parameters mimicking what a user might
     * submit on the public search page.
     *
     * @return array<string, mixed>
     */
    private function randomFilters(\Faker\Generator $faker): array
    {
        $filters = [];

        $jobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];
        $locationTypes = ['Remote', 'On-site', 'Hybrid'];

        // Each filter has a ~40% chance of being present
        if ($faker->boolean(40)) {
            $filters['job_type'] = $faker->randomElement($jobTypes);
        }

        if ($faker->boolean(40)) {
            $filters['location_type'] = $faker->randomElement($locationTypes);
        }

        if ($faker->boolean(30)) {
            $filters['salary_min'] = $faker->numberBetween(30000, 80000);
        }

        if ($faker->boolean(30)) {
            $filters['salary_max'] = $faker->numberBetween(80000, 200000);
        }

        if ($faker->boolean(30)) {
            // Use a short substring that might match company names
            $filters['company_name'] = $faker->randomElement([
                $faker->lexify('???'),
                $faker->randomLetter(),
                'Inc',
                'LLC',
                'Corp',
            ]);
        }

        if ($faker->boolean(30)) {
            $filters['search'] = $faker->randomElement([
                $faker->word(),
                $faker->lexify('????'),
                'developer',
                'manager',
                'engineer',
            ]);
        }

        return $filters;
    }

    /**
     * Apply the same filter logic as JobController::index() to a query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string, mixed> $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['job_type'])) {
            $query->where('job_type', $filters['job_type']);
        }

        if (!empty($filters['location_type'])) {
            $query->where('location_type', $filters['location_type']);
        }

        if (!empty($filters['salary_min'])) {
            $query->where('salary_max', '>=', (int) $filters['salary_min']);
        }

        if (!empty($filters['salary_max'])) {
            $query->where('salary_min', '<=', (int) $filters['salary_max']);
        }

        if (!empty($filters['company_name'])) {
            $query->where('company_name', 'LIKE', '%' . $filters['company_name'] . '%');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Property 22: For any random combination of search/filter parameters,
     * querying through JobListing::active() with those filters applied returns
     * ONLY listings with status "active". No draft or closed listing ever
     * appears.
     *
     * **Validates: Requirements 15.2, 15.5**
     */
    public function test_property_only_active_listings_in_public_search(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250122);

        // Seed a dataset with mixed statuses
        $this->seedListings($faker, 30);

        // Verify we have listings of each status to make the test meaningful
        $this->assertGreaterThan(0, JobListing::where('status', 'draft')->count(), 'Seed must include draft listings');
        $this->assertGreaterThan(0, JobListing::where('status', 'active')->count(), 'Seed must include active listings');
        $this->assertGreaterThan(0, JobListing::where('status', 'closed')->count(), 'Seed must include closed listings');

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $filters = $this->randomFilters($faker);

            // Replicate the controller's query: start with active() scope, then apply filters
            $query = JobListing::active();
            $query = $this->applyFilters($query, $filters);
            $results = $query->get();

            // Assert every returned listing has status "active"
            foreach ($results as $listing) {
                $this->assertSame(
                    'active',
                    $listing->status,
                    sprintf(
                        'Iteration %d: listing ID %d has status "%s" but should be "active". Filters: %s',
                        $i,
                        $listing->id,
                        $listing->status,
                        json_encode($filters)
                    )
                );
            }

            // Also verify no draft/closed listing sneaked in by checking IDs
            $resultIds = $results->pluck('id')->toArray();
            $draftOrClosedIds = JobListing::whereIn('status', ['draft', 'closed'])
                ->pluck('id')
                ->toArray();

            $intersection = array_intersect($resultIds, $draftOrClosedIds);
            $this->assertEmpty(
                $intersection,
                sprintf(
                    'Iteration %d: draft/closed listing IDs %s appeared in results. Filters: %s',
                    $i,
                    json_encode(array_values($intersection)),
                    json_encode($filters)
                )
            );
        }
    }

    /**
     * Property 22 (exhaustive): With NO filters applied, the active() scope
     * alone guarantees only active listings are returned.
     *
     * **Validates: Requirements 15.2, 15.5**
     */
    public function test_property_unfiltered_query_returns_only_active(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(55555);

        $this->seedListings($faker, 25);

        $results = JobListing::active()->get();

        foreach ($results as $listing) {
            $this->assertSame(
                'active',
                $listing->status,
                sprintf('Listing ID %d has status "%s" but only "active" should appear.', $listing->id, $listing->status)
            );
        }

        // Confirm that draft/closed listings exist but are excluded
        $totalAll = JobListing::count();
        $totalActive = $results->count();
        $totalNonActive = JobListing::whereIn('status', ['draft', 'closed'])->count();

        $this->assertSame($totalAll, $totalActive + $totalNonActive);
        $this->assertGreaterThan(0, $totalNonActive, 'Test requires non-active listings to be meaningful');
    }
}
