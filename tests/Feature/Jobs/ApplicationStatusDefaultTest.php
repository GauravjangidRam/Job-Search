<?php

namespace Tests\Feature\Jobs;

use App\Http\Controllers\JobController;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 20: New application defaults to "applied" status
 *
 * For any newly created JobApplication, the initial Application_Status is
 * "applied". The job_applications.status column carries a database default of
 * 'applied' (migration 2025_01_04_000004), and JobController::submitApplication
 * never sets the status column explicitly, so a freshly created application is
 * always persisted with status "applied".
 *
 * This property is exercised from two complementary angles:
 *   1. Model-level: JobApplication::create([...]) WITHOUT a status, reloaded
 *      from the database, always has status === 'applied'.
 *   2. HTTP-level: submitting a valid application through the controller (with
 *      the application routes registered in setUp(), mirroring the other
 *      application tests) always persists a JobApplication with status
 *      "applied".
 *
 * **Validates: Requirements 14.2**
 */
class ApplicationStatusDefaultTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations per property (design requires >= 100).
     */
    private const ITERATIONS = 120;

    protected function setUp(): void
    {
        parent::setUp();

        // The application routes are registered globally in a later task (17.1);
        // register them here so the controller can be exercised end-to-end in
        // isolation, mirroring the other application feature tests.
        Route::middleware('web')->group(function () {
            Route::get('/jobs/{job}/apply', [JobController::class, 'apply'])
                ->name('jobs.apply');
            Route::post('/jobs/{job}/apply', [JobController::class, 'submitApplication'])
                ->name('jobs.submitApplication');
        });

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();

        // Resumes are stored on the "local" disk by FileUploadService, and the
        // notification mail is faked so submission never depends on a real mailer.
        Storage::fake('local');
        Mail::fake();
    }

    /**
     * Create a fresh seeker user with a known email.
     */
    private function createSeeker(\Faker\Generator $faker): User
    {
        return User::factory()->create([
            'role' => 'seeker',
            'email' => $faker->unique()->safeEmail(),
        ]);
    }

    /**
     * Create a fresh active job listing with all required columns populated.
     */
    private function createJobListing(\Faker\Generator $faker): JobListing
    {
        $min = $faker->numberBetween(30000, 90000);
        $max = $min + $faker->numberBetween(5000, 60000);

        return JobListing::create([
            'title' => $faker->jobTitle(),
            'company_name' => $faker->company(),
            'location' => $faker->city(),
            'salary_min' => $min,
            'salary_max' => $max,
            'job_type' => $faker->randomElement(['Full-time', 'Part-time', 'Contract', 'Internship']),
            'location_type' => $faker->randomElement(['Remote', 'On-site', 'Hybrid']),
            'description' => $faker->paragraph(),
            'skills' => $faker->randomElements(['PHP', 'Laravel', 'Vue', 'React', 'SQL', 'AWS', 'Docker'], $faker->numberBetween(1, 4)),
            'status' => 'active',
        ]);
    }

    /**
     * Property 20 (model-level): a JobApplication created WITHOUT an explicit
     * status defaults to "applied" once reloaded from the database.
     *
     * **Validates: Requirements 14.2**
     */
    public function test_property_model_create_without_status_defaults_to_applied(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250120);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $seeker = $this->createSeeker($faker);
            $job = $this->createJobListing($faker);

            // Build a randomized but valid attribute set that NEVER includes a
            // status, so the database default is what determines the value.
            $attributes = [
                'user_id' => $seeker->id,
                'job_listing_id' => $job->id,
                'applicant_name' => $faker->name(),
                'applicant_email' => $faker->safeEmail(),
                'applicant_phone' => $faker->numerify(str_repeat('#', $faker->numberBetween(7, 15))),
                'resume_path' => 'resumes/'.$faker->uuid().'.pdf',
                'cover_letter' => $faker->boolean() ? $faker->paragraph() : null,
                'additional_info' => $faker->boolean() ? $faker->sentence() : null,
                'status_updated_at' => now(),
            ];

            $application = JobApplication::create($attributes);

            // Reload from the database to assert the persisted (default) value
            // rather than any in-memory attribute Eloquent may have inferred.
            $fresh = JobApplication::findOrFail($application->id);

            $this->assertSame(
                'applied',
                $fresh->status,
                sprintf('Iteration %d: a newly created application must default to "applied".', $i)
            );
        }
    }

    /**
     * Property 20 (HTTP-level): submitting a valid application through the
     * controller always persists a JobApplication with status "applied",
     * because the controller never sets the status explicitly.
     *
     * **Validates: Requirements 14.2**
     */
    public function test_property_http_submission_persists_applied_status(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(987654);

        // A focused subset of iterations keeps the HTTP round-trip fast while
        // still exercising the property across many randomized inputs.
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $seeker = $this->createSeeker($faker);
            $job = $this->createJobListing($faker);

            $payload = [
                'applicant_name' => $faker->name(),
                'applicant_email' => $faker->safeEmail(),
                'applicant_phone' => $faker->numerify(str_repeat('#', $faker->numberBetween(7, 15))),
                'resume' => UploadedFile::fake()->create(
                    'resume.pdf',
                    $faker->numberBetween(1, 4000),
                    'application/pdf'
                ),
                'cover_letter' => $faker->boolean() ? $faker->paragraph() : null,
                'additional_info' => $faker->boolean() ? $faker->sentence() : null,
            ];

            $response = $this->actingAs($seeker)->post("/jobs/{$job->id}/apply", $payload);

            // A validation failure would skip creation entirely; surface it.
            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('jobs.apply', $job));

            $application = JobApplication::where('user_id', $seeker->id)
                ->where('job_listing_id', $job->id)
                ->firstOrFail();

            $this->assertSame(
                'applied',
                $application->status,
                sprintf('Iteration %d: a submitted application must have status "applied".', $i)
            );
        }
    }

    /**
     * Example: the simplest possible creation (only the required foreign keys)
     * still defaults to "applied".
     *
     * **Validates: Requirements 14.2**
     */
    public function test_minimal_create_defaults_to_applied(): void
    {
        $faker = \Faker\Factory::create();

        $seeker = $this->createSeeker($faker);
        $job = $this->createJobListing($faker);

        $application = JobApplication::create([
            'user_id' => $seeker->id,
            'job_listing_id' => $job->id,
        ]);

        $this->assertSame('applied', $application->fresh()->status);
    }
}
