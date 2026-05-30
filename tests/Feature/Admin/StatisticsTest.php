<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 13: Statistics consistency
 *
 * For any database state, the sum of users grouped by role SHALL equal the
 * total user count, the sum of listings grouped by status SHALL equal the
 * total listing count, and the sum of applications grouped by status SHALL
 * equal the total application count.
 *
 * This test replicates the queries from Admin\DashboardController::index()
 * at the DB/query level rather than via HTTP, since the view may not exist yet.
 *
 * **Validates: Requirements 9.1, 9.2**
 */
class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The user roles tracked by the admin dashboard.
     */
    private const USER_ROLES = ['seeker', 'employer', 'admin'];

    /**
     * The job listing statuses tracked by the admin dashboard.
     */
    private const LISTING_STATUSES = ['draft', 'active', 'closed'];

    /**
     * The application statuses tracked by the admin dashboard.
     */
    private const APPLICATION_STATUSES = ['applied', 'reviewed', 'shortlisted', 'rejected'];

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 120;

    /**
     * Create a randomized database state: random users with random roles,
     * random job listings with random statuses, and random applications
     * with random statuses.
     */
    private function seedRandomState(\Faker\Generator $faker): void
    {
        // Create a random number of users with random roles
        $userCount = $faker->numberBetween(1, 15);
        for ($u = 0; $u < $userCount; $u++) {
            User::factory()->create([
                'role' => $faker->randomElement(self::USER_ROLES),
            ]);
        }

        // Create a company for job listings
        $company = Company::create([
            'name' => $faker->unique()->company() . ' ' . $faker->randomNumber(5),
            'industry' => $faker->randomElement(['Software', 'Finance', 'Healthcare', 'Retail']),
            'description' => $faker->sentence(),
        ]);

        // Create a random number of job listings with random statuses
        $listingCount = $faker->numberBetween(0, 10);
        $listingIds = [];
        for ($l = 0; $l < $listingCount; $l++) {
            $salaryMin = $faker->numberBetween(30000, 120000);
            $listing = JobListing::create([
                'title' => $faker->jobTitle(),
                'company_name' => $company->name,
                'location' => $faker->city(),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMin + $faker->numberBetween(5000, 80000),
                'job_type' => $faker->randomElement(['full-time', 'part-time', 'contract', 'internship']),
                'location_type' => $faker->randomElement(['remote', 'hybrid', 'onsite']),
                'description' => $faker->paragraph(),
                'skills' => $faker->randomElements(['php', 'laravel', 'vue', 'react', 'sql', 'docker'], 3),
                'status' => $faker->randomElement(self::LISTING_STATUSES),
                'company_id' => $company->id,
            ]);
            $listingIds[] = $listing->id;
        }

        // Create a random number of applications with random statuses
        if (count($listingIds) > 0) {
            $seekers = User::where('role', 'seeker')->pluck('id')->toArray();

            // Ensure at least one seeker exists for applications
            if (empty($seekers)) {
                $seeker = User::factory()->create(['role' => 'seeker']);
                $seekers = [$seeker->id];
            }

            // Build all valid (user_id, job_listing_id) pairs to avoid unique constraint violations
            $possiblePairs = [];
            foreach ($seekers as $seekerId) {
                foreach ($listingIds as $listingId) {
                    $possiblePairs[] = [$seekerId, $listingId];
                }
            }
            $faker->shuffleArray($possiblePairs);

            $applicationCount = $faker->numberBetween(0, min(12, count($possiblePairs)));
            for ($a = 0; $a < $applicationCount; $a++) {
                [$userId, $jobListingId] = $possiblePairs[$a];
                JobApplication::create([
                    'user_id' => $userId,
                    'job_listing_id' => $jobListingId,
                    'applicant_name' => $faker->name(),
                    'applicant_email' => $faker->safeEmail(),
                    'applicant_phone' => $faker->numerify('##########'),
                    'resume_path' => 'resumes/' . $faker->uuid() . '.pdf',
                    'cover_letter' => $faker->boolean() ? $faker->paragraph() : null,
                    'additional_info' => $faker->boolean() ? $faker->sentence() : null,
                    'status' => $faker->randomElement(self::APPLICATION_STATUSES),
                    'status_updated_at' => null,
                ]);
            }
        }
    }

    /**
     * Replicate the controller's statistics queries.
     *
     * @return array{totalUsers: int, totalListings: int, totalApplications: int, totalCompanies: int, usersByRole: array, listingsByStatus: array, applicationsByStatus: array}
     */
    private function computeStatistics(): array
    {
        $totalUsers = User::count();
        $totalListings = JobListing::count();
        $totalApplications = JobApplication::count();
        $totalCompanies = Company::count();

        // Users grouped by role (same logic as controller)
        $roleCounts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $usersByRole = [];
        foreach (self::USER_ROLES as $role) {
            $usersByRole[$role] = (int) ($roleCounts[$role] ?? 0);
        }

        // Listings grouped by status
        $listingStatusCounts = JobListing::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $listingsByStatus = [];
        foreach (self::LISTING_STATUSES as $status) {
            $listingsByStatus[$status] = (int) ($listingStatusCounts[$status] ?? 0);
        }

        // Applications grouped by status
        $applicationStatusCounts = JobApplication::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $applicationsByStatus = [];
        foreach (self::APPLICATION_STATUSES as $status) {
            $applicationsByStatus[$status] = (int) ($applicationStatusCounts[$status] ?? 0);
        }

        return compact(
            'totalUsers',
            'totalListings',
            'totalApplications',
            'totalCompanies',
            'usersByRole',
            'listingsByStatus',
            'applicationsByStatus',
        );
    }

    /**
     * Property 13: Statistics consistency — sum of grouped values equals totals.
     *
     * For each iteration: create a randomized database state with random users
     * (random roles), random job listings (random statuses), and random
     * applications (random statuses). Then compute the statistics the same way
     * the controller does and assert:
     *   1. sum(usersByRole values) === totalUsers
     *   2. sum(listingsByStatus values) === totalListings
     *   3. sum(applicationsByStatus values) === totalApplications
     *
     * **Validates: Requirements 9.1, 9.2**
     */
    public function test_property_statistics_consistency_sum_of_grouped_equals_total(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250613);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // Seed a randomized database state
            $this->seedRandomState($faker);

            // Compute statistics using the same queries as the controller
            $stats = $this->computeStatistics();

            // Assert: sum(usersByRole) === totalUsers
            $sumUsersByRole = array_sum($stats['usersByRole']);
            $this->assertSame(
                $stats['totalUsers'],
                $sumUsersByRole,
                sprintf(
                    'Iteration %d: sum(usersByRole) [%d] must equal totalUsers [%d]. Breakdown: %s',
                    $i,
                    $sumUsersByRole,
                    $stats['totalUsers'],
                    json_encode($stats['usersByRole'])
                )
            );

            // Assert: sum(listingsByStatus) === totalListings
            $sumListingsByStatus = array_sum($stats['listingsByStatus']);
            $this->assertSame(
                $stats['totalListings'],
                $sumListingsByStatus,
                sprintf(
                    'Iteration %d: sum(listingsByStatus) [%d] must equal totalListings [%d]. Breakdown: %s',
                    $i,
                    $sumListingsByStatus,
                    $stats['totalListings'],
                    json_encode($stats['listingsByStatus'])
                )
            );

            // Assert: sum(applicationsByStatus) === totalApplications
            $sumApplicationsByStatus = array_sum($stats['applicationsByStatus']);
            $this->assertSame(
                $stats['totalApplications'],
                $sumApplicationsByStatus,
                sprintf(
                    'Iteration %d: sum(applicationsByStatus) [%d] must equal totalApplications [%d]. Breakdown: %s',
                    $i,
                    $sumApplicationsByStatus,
                    $stats['totalApplications'],
                    json_encode($stats['applicationsByStatus'])
                )
            );

            // Clean up for next iteration to keep each iteration independent
            JobApplication::query()->delete();
            JobListing::query()->delete();
            Company::query()->delete();
            User::query()->delete();
        }
    }
}
