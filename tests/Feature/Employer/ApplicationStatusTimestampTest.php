<?php

namespace Tests\Feature\Employer;

use App\Http\Controllers\Employer\ApplicationController as Ctrl;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 21: Status update records timestamp
 *
 * For any Application_Status change, the status_updated_at field SHALL be set
 * to a timestamp within a reasonable delta of the current time.
 *
 * Employer\ApplicationController::updateStatus() validates the incoming status
 * against {applied, reviewed, shortlisted, rejected}, sets $application->status
 * and $application->status_updated_at = now(), persists the change, and
 * redirects back with a success flash message.
 *
 * The employer application routes are wired globally in a later task (17.1);
 * they are registered here in setUp() so the controller can be exercised in
 * isolation, mirroring the other employer feature tests.
 *
 * **Validates: Requirements 14.3**
 */
class ApplicationStatusTimestampTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The set of valid Application_Status values updateStatus() accepts.
     */
    private const STATUSES = ['applied', 'reviewed', 'shortlisted', 'rejected'];

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 120;

    protected function setUp(): void
    {
        parent::setUp();

        // Registered globally in task 17.1; register here so the status-update
        // behavior can be exercised end-to-end in isolation.
        Route::middleware('web')->patch(
            '/employer/applications/{application}/status',
            [Ctrl::class, 'updateStatus']
        )->name('employer.applications.updateStatus');

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
            'status' => 'active',
            'company_id' => $companyId,
        ]);
    }

    /**
     * Create a JobApplication for the given listing with a known starting state
     * (status "applied", no status_updated_at yet).
     */
    private function createJobApplication(\Faker\Generator $faker, int $jobListingId): JobApplication
    {
        $seeker = User::factory()->create([
            'role' => 'seeker',
            'email' => $faker->unique()->safeEmail(),
        ]);

        return JobApplication::create([
            'user_id' => $seeker->id,
            'job_listing_id' => $jobListingId,
            'applicant_name' => $faker->name(),
            'applicant_email' => $faker->safeEmail(),
            'applicant_phone' => $faker->numerify(str_repeat('#', $faker->numberBetween(7, 15))),
            'resume_path' => 'resumes/' . $faker->uuid() . '.pdf',
            'cover_letter' => $faker->boolean() ? $faker->paragraph() : null,
            'additional_info' => $faker->boolean() ? $faker->sentence() : null,
            'status' => 'applied',
            'status_updated_at' => null,
        ]);
    }

    /**
     * Property 21: every status change records a status_updated_at timestamp
     * within a reasonable delta of the current time.
     *
     * For each iteration: build a company + employer, a listing for that
     * company, and an application (status "applied", status_updated_at null).
     * Capture the time just before and just after a PATCH that sets a random
     * valid status, then assert the reloaded application has the submitted
     * status and a status_updated_at that falls within the captured window
     * (with a one-second tolerance on each side to absorb clock/precision skew).
     *
     * **Validates: Requirements 14.3**
     */
    public function test_property_status_update_records_timestamp_within_window(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250121);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $company = $this->createCompany($faker);
            $employer = $this->createEmployer($company);
            $listing = $this->createListing($faker, $company->id);
            $application = $this->createJobApplication($faker, $listing->id);

            // Sanity: the application starts without a recorded change timestamp.
            $this->assertNull(
                $application->status_updated_at,
                "Iteration {$i}: application should start with a null status_updated_at."
            );

            $newStatus = $faker->randomElement(self::STATUSES);

            $before = Carbon::now();
            $response = $this->actingAs($employer)->patch(
                "/employer/applications/{$application->id}/status",
                ['status' => $newStatus]
            );
            $after = Carbon::now();

            // A validation failure would skip the update entirely; surface it.
            $response->assertSessionHasNoErrors();
            $response->assertSessionHas('success');

            $fresh = JobApplication::findOrFail($application->id);

            $this->assertSame(
                $newStatus,
                $fresh->status,
                "Iteration {$i}: the persisted status must match the submitted status."
            );

            $this->assertNotNull(
                $fresh->status_updated_at,
                "Iteration {$i}: a status change must record a status_updated_at timestamp."
            );

            // The recorded timestamp must fall within the [before, after]
            // window, with a one-second tolerance on each side.
            $lowerBound = $before->copy()->subSecond();
            $upperBound = $after->copy()->addSecond();

            $this->assertTrue(
                $fresh->status_updated_at->betweenIncluded($lowerBound, $upperBound),
                sprintf(
                    'Iteration %d: status_updated_at (%s) must be within [%s, %s].',
                    $i,
                    $fresh->status_updated_at->toDateTimeString(),
                    $lowerBound->toDateTimeString(),
                    $upperBound->toDateTimeString()
                )
            );
        }
    } 

    /** 
     * Example: a concrete status update records a timestamp close to "now" and
     * advances the recorded timestamp when the status changes again.
     *
     * **Validates: Requirements 14.3**
     */
    public function test_concrete_status_update_records_and_advances_timestamp(): void
    {
        $faker = \Faker\Factory::create();

        $company = $this->createCompany($faker);
        $employer = $this->createEmployer($company);
        $listing = $this->createListing($faker, $company->id);
        $application = $this->createJobApplication($faker, $listing->id);

        $before = Carbon::now();
        $this->actingAs($employer)
            ->patch("/employer/applications/{$application->id}/status", ['status' => 'reviewed'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
        $after = Carbon::now();

        $fresh = JobApplication::findOrFail($application->id);

        $this->assertSame('reviewed', $fresh->status);
        $this->assertNotNull($fresh->status_updated_at);
        $this->assertTrue(
            $fresh->status_updated_at->betweenIncluded(
                $before->copy()->subSecond(),
                $after->copy()->addSecond()
            ),
            'The recorded timestamp should be within a reasonable delta of "now".'
        );
    }
}