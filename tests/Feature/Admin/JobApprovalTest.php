<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 11: Listing approval state transition
 *
 * For any JobListing with status "draft", when an Admin approves it, the
 * listing's status becomes "active" and the listing appears in public search
 * results (scopeActive).
 *
 * **Validates: Requirements 7.2, 15.4**
 */
class JobApprovalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 120;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the admin approve route since routes are not registered yet (task 17.1)
        Route::middleware('web')->post('/admin/jobs/{job}/approve', [
            \App\Http\Controllers\Admin\JobListingController::class,
            'approve',
        ])->name('admin.jobs.approve');

        // Refresh the route name lookups so route() helper works
        Route::getRoutes()->refreshNameLookups();
    }

    /**
     * Property 11: Listing approval state transition — approving a draft listing
     * sets status to "active" and makes it visible via scopeActive.
     *
     * Each iteration:
     * 1. Create an admin user
     * 2. Create a JobListing with status "draft" and randomized attributes
     * 3. Acting as admin, POST to /admin/jobs/{id}/approve
     * 4. Reload from DB and assert status === 'active'
     * 5. Assert JobListing::active()->where('id', $id)->exists() is true
     *
     * **Validates: Requirements 7.2, 15.4**
     */
    public function test_property_listing_approval_state_transition(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250613);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // Create an admin user
            $admin = User::factory()->create([
                'role' => 'admin',
            ]);

            // Create a company for the job listing
            $company = Company::create([
                'name' => $faker->company() . ' ' . $faker->randomNumber(5),
                'industry' => $faker->randomElement(['Software', 'Finance', 'Healthcare', 'Retail', 'Education']),
                'description' => $faker->sentence(),
            ]);

            // Create a JobListing with status "draft" and randomized attributes
            $salaryMin = $faker->numberBetween(25000, 150000);
            $listing = JobListing::create([
                'title' => $faker->jobTitle(),
                'company_name' => $company->name,
                'location' => $faker->city() . ', ' . $faker->stateAbbr(),
                'salary_min' => $salaryMin,
                'salary_max' => $salaryMin + $faker->numberBetween(5000, 80000),
                'job_type' => $faker->randomElement(['full-time', 'part-time', 'contract', 'internship']),
                'location_type' => $faker->randomElement(['remote', 'hybrid', 'onsite']),
                'description' => $faker->paragraph(),
                'skills' => $faker->randomElements(['php', 'laravel', 'vue', 'react', 'sql', 'docker', 'python', 'java', 'go'], $faker->numberBetween(1, 5)),
                'status' => 'draft',
                'company_id' => $company->id,
            ]);

            // Verify the listing starts as draft
            $this->assertSame('draft', $listing->status, "Iteration {$i}: listing should start as draft");

            // Acting as admin, POST to approve the listing
            $response = $this->actingAs($admin)->post("/admin/jobs/{$listing->id}/approve");

            // Assert the response is a redirect (back)
            $response->assertStatus(302);

            // Reload from DB and assert status === 'active'
            $listing->refresh();
            $this->assertSame(
                'active',
                $listing->status,
                sprintf(
                    'Iteration %d: After approval, listing (id=%d, title="%s") status should be "active", got "%s"',
                    $i,
                    $listing->id,
                    $listing->title,
                    $listing->status
                )
            );

            // Assert the listing now appears in the active scope (public search results)
            $this->assertTrue(
                JobListing::active()->where('id', $listing->id)->exists(),
                sprintf(
                    'Iteration %d: After approval, listing (id=%d) should appear in JobListing::active() scope',
                    $i,
                    $listing->id
                )
            );

            // Clean up for next iteration
            JobListing::query()->delete();
            Company::query()->delete();
            User::query()->delete();
        }
    }
}
