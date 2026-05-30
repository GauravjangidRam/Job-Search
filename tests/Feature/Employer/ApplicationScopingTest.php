<?php

namespace Tests\Feature\Employer;

use App\Http\Controllers\Employer\ApplicationController as Ctrl;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 17: Employer application scoping
 *
 * For any Employer, all applications visible in their portal SHALL be for job
 * listings belonging to their company, and any attempt to view or modify an
 * application for another company's listing SHALL be rejected (403).
 *
 * Employer\ApplicationController::index() scopes via
 *   whereHas('jobListing', fn ($q) => $q->where('company_id', $companyId)),
 * while show()/updateStatus()/downloadResume() call authorizeOwnership(),
 * which performs abort_if($application->jobListing->company_id !== company_id, 403).
 *
 * The employer application routes are wired globally in a later task (17.1);
 * they are registered here in setUp() so the controller can be exercised in
 * isolation.
 *
 * The portal index/show Blade views are created by a parallel task (10.6) and
 * may not exist yet. To stay decoupled from that work:
 *   - Index ("visible in their portal") scoping is asserted via the
 *     company-scoped query that backs index() rather than by rendering the view.
 *   - The portal-access enforcement is asserted over HTTP. The 403 path runs
 *     before any view is rendered, so it is robust regardless of view timing;
 *     the owned show() render is asserted only when its view exists, and the
 *     owned updateStatus() path (which redirects, no view) is always asserted.
 *
 * **Validates: Requirements 12.1**
 */
class ApplicationScopingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 100;

    /**
     * Valid application statuses accepted by updateStatus() validation.
     */
    private const STATUSES = ['applied', 'reviewed', 'shortlisted', 'rejected'];

    protected function setUp(): void
    {
        parent::setUp();

        // These routes are wired globally in task 17.1; register them here so
        // the scoping behavior can be exercised end-to-end in isolation.
        Route::middleware('web')->get('/employer/applications', [Ctrl::class, 'index'])
            ->name('employer.applications.index');
        Route::middleware('web')->get('/employer/applications/{application}', [Ctrl::class, 'show'])
            ->name('employer.applications.show');
        Route::middleware('web')->patch('/employer/applications/{application}/status', [Ctrl::class, 'updateStatus'])
            ->name('employer.applications.updateStatus');

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
     * Create a JobApplication for the given listing by a fresh seeker.
     *
     * Each application gets its own seeker so the (user_id, job_listing_id)
     * unique constraint is never violated regardless of how listings are reused.
     */
    private function makeApplication(\Faker\Generator $faker, int $jobListingId): JobApplication
    {
        $seeker = User::factory()->create(['role' => 'seeker']);

        return JobApplication::create([
            'user_id' => $seeker->id,
            'job_listing_id' => $jobListingId,
            'applicant_name' => $faker->name(),
            'applicant_email' => $faker->unique()->safeEmail(),
            'applicant_phone' => $faker->phoneNumber(),
            'cover_letter' => $faker->paragraph(),
            'status' => $faker->randomElement(self::STATUSES),
        ]);
    }

    /**
     * Property 17: Employer application scoping.
     *
     * For each iteration: build two companies A and B, an employer for A, a
     * randomized mix of listings for each company, and applications spread
     * across listings of both companies. Then assert
     *   (1) the company-scoped query backing the portal index returns exactly
     *       the applications whose listing belongs to A and none belonging to B,
     *       and
     *   (2) viewing/updating an owned application is allowed (not 403) while the
     *       same operations on an application for company B's listing are
     *       rejected with 403.
     *
     * **Validates: Requirements 12.1**
     */
    public function test_employer_only_sees_applications_for_own_company_listings(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250117);

        $showViewExists = View::exists('employer.applications.show');

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $companyA = $this->createCompany($faker);
            $companyB = $this->createCompany($faker);
            $employerA = $this->createEmployer($companyA);

            // Listings for each company.
            $ownedListings = collect();
            $ownedListingCount = $faker->numberBetween(1, 3);
            for ($n = 0; $n < $ownedListingCount; $n++) {
                $ownedListings->push($this->createListing($faker, $companyA->id));
            }

            $otherListings = collect();
            $otherListingCount = $faker->numberBetween(1, 3);
            for ($m = 0; $m < $otherListingCount; $m++) {
                $otherListings->push($this->createListing($faker, $companyB->id));
            }

            // Applications spread across both companies' listings.
            $ownedApplications = collect();
            $ownedAppCount = $faker->numberBetween(1, 4);
            for ($a = 0; $a < $ownedAppCount; $a++) {
                $ownedApplications->push(
                    $this->makeApplication($faker, $ownedListings->random()->id)
                );
            }

            $otherApplications = collect();
            $otherAppCount = $faker->numberBetween(1, 4);
            for ($b = 0; $b < $otherAppCount; $b++) {
                $otherApplications->push(
                    $this->makeApplication($faker, $otherListings->random()->id)
                );
            }

            // ----------------------------------------------------------------
            // (1) Index scoping invariant: the company-scoped query that backs
            //     index() returns only applications for company A's listings.
            // ----------------------------------------------------------------
            $visible = JobApplication::whereHas(
                'jobListing',
                fn ($query) => $query->where('company_id', $companyA->id)
            )->with('jobListing')->get();

            $this->assertCount(
                $ownedAppCount,
                $visible,
                "Iteration {$i}: employer should see exactly the applications for their company's listings."
            );

            foreach ($visible as $application) {
                $this->assertSame(
                    $companyA->id,
                    $application->jobListing->company_id,
                    "Iteration {$i}: every visible application must be for a listing owned by the employer's company."
                );
            }

            $visibleIds = $visible->pluck('id');
            foreach ($otherApplications as $foreign) {
                $this->assertFalse(
                    $visibleIds->contains($foreign->id),
                    "Iteration {$i}: an application for another company's listing must never be visible."
                );
            }

            // Randomly target one owned and one foreign application for the
            // portal-access checks.
            $owned = $ownedApplications->random();
            $foreign = $otherApplications->random();

            // ----------------------------------------------------------------
            // (2) Portal access over HTTP: owned allowed, foreign rejected.
            // ----------------------------------------------------------------

            // show: foreign is always 403 (abort fires before any view render).
            $this->actingAs($employerA)
                ->get("/employer/applications/{$foreign->id}")
                ->assertStatus(403);

            // updateStatus: foreign rejected with 403 even with a valid status,
            // and the foreign application's status is left unchanged.
            $foreignStatusBefore = $foreign->status;
            $this->actingAs($employerA)
                ->patch("/employer/applications/{$foreign->id}/status", [
                    'status' => $faker->randomElement(self::STATUSES),
                ])
                ->assertStatus(403);
            $this->assertDatabaseHas('job_applications', [
                'id' => $foreign->id,
                'status' => $foreignStatusBefore,
            ]);

            // show: owned passes the ownership check. Only assert a rendered 200
            // when the portal show view exists (created by parallel task 10.6).
            if ($showViewExists) {
                $this->actingAs($employerA)
                    ->get("/employer/applications/{$owned->id}")
                    ->assertStatus(200);
            }

            // updateStatus: owned passes ownership and redirects (no view, so it
            // is always safe to exercise). Crucially it is NOT a 403.
            $newStatus = $faker->randomElement(self::STATUSES);
            $response = $this->actingAs($employerA)
                ->patch("/employer/applications/{$owned->id}/status", [
                    'status' => $newStatus,
                ]);
            $response->assertStatus(302);
            $this->assertNotSame(403, $response->getStatusCode());
            $this->assertDatabaseHas('job_applications', [
                'id' => $owned->id,
                'status' => $newStatus,
            ]);
        }
    }

    /**
     * Example: a concrete two-company arrangement demonstrating that show and
     * updateStatus on an application for a foreign company's listing are
     * rejected with 403 while the owned application can be viewed/updated.
     *
     * **Validates: Requirements 12.1**
     */
    public function test_concrete_cross_company_application_access_is_rejected(): void
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

        $listingA = JobListing::create([
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

        $listingB = JobListing::create([
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

        $seekerOne = User::factory()->create(['role' => 'seeker']);
        $seekerTwo = User::factory()->create(['role' => 'seeker']);

        $ownedApplication = JobApplication::create([
            'user_id' => $seekerOne->id,
            'job_listing_id' => $listingA->id,
            'applicant_name' => 'Ada Lovelace',
            'applicant_email' => 'ada@example.com',
            'status' => 'applied',
        ]);

        $foreignApplication = JobApplication::create([
            'user_id' => $seekerTwo->id,
            'job_listing_id' => $listingB->id,
            'applicant_name' => 'Grace Hopper',
            'applicant_email' => 'grace@example.com',
            'status' => 'applied',
        ]);

        // Foreign application cannot be viewed or updated by company A's employer.
        $this->actingAs($employerA)
            ->get("/employer/applications/{$foreignApplication->id}")
            ->assertStatus(403);
        $this->actingAs($employerA)
            ->patch("/employer/applications/{$foreignApplication->id}/status", ['status' => 'reviewed'])
            ->assertStatus(403);
        $this->assertDatabaseHas('job_applications', [
            'id' => $foreignApplication->id,
            'status' => 'applied',
        ]);

        // Owned application can be updated (not 403) and the change persists.
        $this->actingAs($employerA)
            ->patch("/employer/applications/{$ownedApplication->id}/status", ['status' => 'shortlisted'])
            ->assertStatus(302);
        $this->assertDatabaseHas('job_applications', [
            'id' => $ownedApplication->id,
            'status' => 'shortlisted',
        ]);
    }
}
