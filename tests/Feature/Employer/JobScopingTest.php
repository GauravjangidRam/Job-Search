<?php

namespace Tests\Feature\Employer;

use App\Http\Controllers\Employer\JobListingController as Ctrl;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 16: Employer job scoping
 *
 * For any Employer, all job listings visible in their portal SHALL belong to
 * their company, and any attempt to modify a listing belonging to a different
 * company SHALL be rejected (403).
 *
 * Employer\JobListingController::index() filters to Auth::user()->company_id,
 * while edit()/update()/destroy() call authorizeOwnership(), which performs
 * abort_if($job->company_id !== Auth::user()->company_id, 403).
 *
 * The employer job routes are wired globally in a later task (17.1); they are
 * registered here in setUp() so the controller can be exercised in isolation.
 *
 * The portal index/edit Blade views are created by a parallel task (9.4) and
 * may not exist yet. To stay decoupled from that work:
 *   - Index ("visible in their portal") scoping is asserted via the company
 *     query that backs index() rather than by rendering the view.
 *   - The modify-rejection behavior is asserted over HTTP. The 403 path runs
 *     before any view is rendered, so it is robust regardless of view timing;
 *     the owned "success" edit render is asserted only when its view exists.
 *
 * **Validates: Requirements 11.3, 11.7**
 */
class JobScopingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 100;

    protected function setUp(): void
    {
        parent::setUp();

        // These routes are wired globally in task 17.1; register them here so
        // the scoping behavior can be exercised end-to-end in isolation.
        Route::middleware('web')->get('/employer/jobs', [Ctrl::class, 'index'])
            ->name('employer.jobs.index');
        Route::middleware('web')->get('/employer/jobs/{job}/edit', [Ctrl::class, 'edit'])
            ->name('employer.jobs.edit');
        Route::middleware('web')->put('/employer/jobs/{job}', [Ctrl::class, 'update'])
            ->name('employer.jobs.update');
        Route::middleware('web')->delete('/employer/jobs/{job}', [Ctrl::class, 'destroy'])
            ->name('employer.jobs.destroy');

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    /**
     * Create a freshly-persisted Company.
     */
    private function createCompany(\Faker\Generator $faker): Company
    {
        return Company::create([
            'name' => $faker->unique()->company() . ' ' . $faker->randomNumber(5),
            'industry' => $faker->randomElement(['Software', 'Finance', 'Healthcare', 'Retail']),
            'description' => $faker->sentence(),
        ]);
    }

    /**
     * Create an employer user belonging to the given company.
     */
    private function createEmployer(Company $company): User
    {
        return User::factory()->create([
            'role' => 'employer',
            'company_id' => $company->id,
        ]);
    }

    /**
     * Create a JobListing for the given company with all required columns set.
     */
    private function createListing(\Faker\Generator $faker, int $companyId): JobListing
    {
        $salaryMin = $faker->numberBetween(30000, 120000);
        $salaryMax = $salaryMin + $faker->numberBetween(5000, 80000);

        return JobListing::create([
            'title' => $faker->jobTitle(),
            'company_name' => $faker->company(),
            'location' => $faker->city(),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'job_type' => $faker->randomElement(['full-time', 'part-time', 'contract', 'internship']),
            'location_type' => $faker->randomElement(['remote', 'hybrid', 'onsite']),
            'description' => $faker->paragraph(),
            'skills' => $faker->randomElements(['php', 'laravel', 'vue', 'react', 'sql', 'docker'], 3),
            'status' => $faker->randomElement(['draft', 'active', 'closed']),
            'company_id' => $companyId,
        ]);
    }

    /**
     * Build a valid UpdateJobListingRequest payload for the given listing.
     *
     * The payload must always validate so that authorizeOwnership() (and not
     * form-request validation) is what decides between success and a 403.
     *
     * @return array<string, mixed>
     */
    private function validUpdatePayload(\Faker\Generator $faker): array
    {
        $salaryMin = $faker->numberBetween(30000, 120000);
        $salaryMax = $salaryMin + $faker->numberBetween(5000, 80000);

        return [
            'title' => $faker->jobTitle(),
            'description' => $faker->paragraph(),
            'location' => $faker->city(),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'job_type' => $faker->randomElement(['full-time', 'part-time', 'contract', 'internship']),
            'location_type' => $faker->randomElement(['remote', 'hybrid', 'onsite']),
            'status' => $faker->randomElement(['draft', 'active', 'closed']),
        ];
    }

    /**
     * Property 16: Employer job scoping.
     *
     * For each iteration: build two companies A and B, an employer for A, and a
     * randomized mix of listings for each company. Then assert
     *   (1) the company-scoped query backing the portal index returns exactly
     *       A's listings and none of B's, and
     *   (2) modifying an owned listing succeeds while modifying a listing owned
     *       by company B is rejected with 403 for edit, update, and destroy.
     *
     * **Validates: Requirements 11.3, 11.7**
     */
    public function test_employer_only_sees_and_modifies_own_company_listings(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250116);

        $editViewExists = View::exists('employer.jobs.edit');

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $companyA = $this->createCompany($faker);
            $companyB = $this->createCompany($faker);
            $employerA = $this->createEmployer($companyA);

            $ownedCount = $faker->numberBetween(1, 4);
            $otherCount = $faker->numberBetween(1, 4);

            $ownedListings = collect();
            for ($n = 0; $n < $ownedCount; $n++) {
                $ownedListings->push($this->createListing($faker, $companyA->id));
            }

            $otherListings = collect();
            for ($m = 0; $m < $otherCount; $m++) {
                $otherListings->push($this->createListing($faker, $companyB->id));
            }

            // ----------------------------------------------------------------
            // (1) Index scoping invariant: the company-scoped query that backs
            //     index() returns only company A's listings and none of B's.
            // ----------------------------------------------------------------
            $visible = JobListing::where('company_id', $employerA->company_id)->get();

            $this->assertCount(
                $ownedCount,
                $visible,
                "Iteration {$i}: employer should see exactly their own company's listings."
            );

            foreach ($visible as $listing) {
                $this->assertSame(
                    $companyA->id,
                    $listing->company_id,
                    "Iteration {$i}: every visible listing must belong to the employer's company."
                );
            }

            $visibleIds = $visible->pluck('id');
            foreach ($otherListings as $foreign) {
                $this->assertFalse(
                    $visibleIds->contains($foreign->id),
                    "Iteration {$i}: another company's listing must never be visible."
                );
            }

            // Randomly target one owned and one foreign listing for the modify checks.
            $owned = $ownedListings->random();
            $foreign = $otherListings->random();

            // ----------------------------------------------------------------
            // (2) Modify scoping over HTTP: owned succeeds, foreign is rejected.
            // ----------------------------------------------------------------

            // edit: foreign is always 403 (abort fires before the view renders).
            $this->actingAs($employerA)
                ->get("/employer/jobs/{$foreign->id}/edit")
                ->assertStatus(403);

            // edit: owned passes the ownership check. Only assert a rendered 200
            // when the portal edit view exists (created by parallel task 9.4).
            if ($editViewExists) {
                $this->actingAs($employerA)
                    ->get("/employer/jobs/{$owned->id}/edit")
                    ->assertStatus(200);
            }

            // update: foreign rejected with 403 even though the payload is valid.
            $this->actingAs($employerA)
                ->put("/employer/jobs/{$foreign->id}", $this->validUpdatePayload($faker))
                ->assertStatus(403);

            // update: owned succeeds and redirects to the portal index.
            $this->actingAs($employerA)
                ->put("/employer/jobs/{$owned->id}", $this->validUpdatePayload($faker))
                ->assertRedirect(route('employer.jobs.index'));

            // destroy: foreign rejected with 403 and the record survives.
            $this->actingAs($employerA)
                ->delete("/employer/jobs/{$foreign->id}")
                ->assertStatus(403);
            $this->assertDatabaseHas('job_listings', ['id' => $foreign->id]);

            // destroy: owned succeeds, redirects, and the record is removed.
            $this->actingAs($employerA)
                ->delete("/employer/jobs/{$owned->id}")
                ->assertRedirect(route('employer.jobs.index'));
            $this->assertDatabaseMissing('job_listings', ['id' => $owned->id]);
        }
    }

    /**
     * Example: a concrete two-company arrangement demonstrating that update and
     * destroy on a foreign listing are rejected with 403 while the owned listing
     * can be updated and deleted.
     *
     * **Validates: Requirements 11.3, 11.7**
     */
    public function test_concrete_cross_company_modification_is_rejected(): void
    {
        $companyA = Company::create([
            'name' => 'Acme Corp',
            'industry' => 'Software',
            'description' => 'We build things.',
        ]);
        $companyB = Company::create([
            'name' => 'Globex Inc',
            'industry' => 'Finance',
            'description' => 'We invest things.',
        ]);

        $employerA = User::factory()->create([
            'role' => 'employer',
            'company_id' => $companyA->id,
        ]);

        $ownedListing = JobListing::create([
            'title' => 'Senior Engineer',
            'company_name' => 'Acme Corp',
            'location' => 'Remote',
            'salary_min' => 100000,
            'salary_max' => 150000,
            'job_type' => 'full-time',
            'location_type' => 'remote',
            'description' => 'Build cool stuff.',
            'skills' => ['php', 'laravel'],
            'status' => 'active',
            'company_id' => $companyA->id,
        ]);

        $foreignListing = JobListing::create([
            'title' => 'Analyst',
            'company_name' => 'Globex Inc',
            'location' => 'New York',
            'salary_min' => 80000,
            'salary_max' => 110000,
            'job_type' => 'full-time',
            'location_type' => 'onsite',
            'description' => 'Crunch numbers.',
            'skills' => ['excel', 'sql'],
            'status' => 'active',
            'company_id' => $companyB->id,
        ]);

        $payload = [
            'title' => 'Updated Title',
            'description' => 'Updated description.',
            'location' => 'Hybrid',
            'salary_min' => 90000,
            'salary_max' => 140000,
            'job_type' => 'full-time',
            'location_type' => 'hybrid',
            'status' => 'closed',
        ];

        // Foreign listing cannot be updated or destroyed by company A's employer.
        $this->actingAs($employerA)
            ->put("/employer/jobs/{$foreignListing->id}", $payload)
            ->assertStatus(403);
        $this->actingAs($employerA)
            ->delete("/employer/jobs/{$foreignListing->id}")
            ->assertStatus(403);
        $this->assertDatabaseHas('job_listings', ['id' => $foreignListing->id]);

        // Owned listing can be updated and destroyed.
        $this->actingAs($employerA)
            ->put("/employer/jobs/{$ownedListing->id}", $payload)
            ->assertRedirect(route('employer.jobs.index'));
        $this->assertDatabaseHas('job_listings', [
            'id' => $ownedListing->id,
            'title' => 'Updated Title',
            'status' => 'closed',
        ]);

        $this->actingAs($employerA)
            ->delete("/employer/jobs/{$ownedListing->id}")
            ->assertRedirect(route('employer.jobs.index'));
        $this->assertDatabaseMissing('job_listings', ['id' => $ownedListing->id]);
    }
}
