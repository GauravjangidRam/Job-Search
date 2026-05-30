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
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 5: Application submission persistence
 *
 * For any valid application data (name, email, phone, resume, cover letter,
 * additional info), when a Seeker submits the application form, the stored
 * job_application record contains all submitted field values unchanged.
 *
 * Validates: Requirements 4.2
 */
class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 120;

    protected function setUp(): void
    {
        parent::setUp();

        // The job apply/submit routes are not registered until task 17.1, so we
        // register them here to exercise the controller through the HTTP layer.
        Route::middleware('web')->get('/jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');
        Route::middleware('web')->post('/jobs/{job}/apply', [JobController::class, 'submitApplication'])->name('jobs.submitApplication');

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    public function test_property_application_submission_persists_all_field_values_unchanged(): void
    {
        Storage::fake('local');
        Mail::fake();

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // A fresh seeker + job each iteration avoids the duplicate-prevention guard.
            $seeker = User::factory()->create(['role' => 'seeker']);
            $job = $this->createActiveJob();

            $payload = $this->randomValidPayload();
            $resume = UploadedFile::fake()->create('resume.pdf', random_int(1, 5120), 'application/pdf');

            $response = $this->actingAs($seeker)->post("/jobs/{$job->id}/apply", array_merge($payload, [
                'resume' => $resume,
            ]));

            $context = 'iteration '.$i.' payload: '.json_encode($payload);

            // A valid submission must succeed (no validation errors) and redirect back to the apply page.
            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('jobs.apply', $job));

            $application = JobApplication::where('user_id', $seeker->id)
                ->where('job_listing_id', $job->id)
                ->first();

            $this->assertNotNull($application, "Application was not persisted for {$context}");

            // Every submitted field value is stored unchanged.
            $this->assertSame($payload['applicant_name'], $application->applicant_name, "applicant_name mismatch for {$context}");
            $this->assertSame($payload['applicant_email'], $application->applicant_email, "applicant_email mismatch for {$context}");
            $this->assertSame($payload['applicant_phone'] ?? null, $application->applicant_phone, "applicant_phone mismatch for {$context}");
            $this->assertSame($payload['cover_letter'] ?? null, $application->cover_letter, "cover_letter mismatch for {$context}");
            $this->assertSame($payload['additional_info'] ?? null, $application->additional_info, "additional_info mismatch for {$context}");

            // The resume is stored and the file actually exists on the faked disk.
            $this->assertNotNull($application->resume_path, "resume_path should be set for {$context}");
            $this->assertTrue(
                Storage::disk('local')->exists($application->resume_path),
                "Stored resume file should exist on disk for {$context}"
            );

            // Ownership and listing association are correct.
            $this->assertSame($seeker->id, $application->user_id, "user_id mismatch for {$context}");
            $this->assertSame($job->id, $application->job_listing_id, "job_listing_id mismatch for {$context}");
        }
    }

    public function test_application_submission_persists_exact_values_example(): void
    {
        Storage::fake('local');
        Mail::fake();

        $seeker = User::factory()->create(['role' => 'seeker']);
        $job = $this->createActiveJob();

        $payload = [
            'applicant_name' => 'Ada Lovelace',
            'applicant_email' => 'ada@example.com',
            'applicant_phone' => '15551234567',
            'cover_letter' => "I am very interested in this role.\nIt aligns with my experience.",
            'additional_info' => 'Available to start immediately.',
        ];

        $response = $this->actingAs($seeker)->post("/jobs/{$job->id}/apply", array_merge($payload, [
            'resume' => UploadedFile::fake()->create('resume.pdf', 512, 'application/pdf'),
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('jobs.apply', $job));

        $application = JobApplication::where('user_id', $seeker->id)
            ->where('job_listing_id', $job->id)
            ->firstOrFail();

        $this->assertSame($payload['applicant_name'], $application->applicant_name);
        $this->assertSame($payload['applicant_email'], $application->applicant_email);
        $this->assertSame($payload['applicant_phone'], $application->applicant_phone);
        $this->assertSame($payload['cover_letter'], $application->cover_letter);
        $this->assertSame($payload['additional_info'], $application->additional_info);
        $this->assertNotNull($application->resume_path);
        $this->assertTrue(Storage::disk('local')->exists($application->resume_path));
    }

    /**
     * Create an active job listing with all required columns populated.
     */
    private function createActiveJob(): JobListing
    {
        return JobListing::create([
            'title' => 'Job '.Str::random(8),
            'company_name' => 'Company '.Str::random(6),
            'location' => 'Remote',
            'salary_min' => 50000,
            'salary_max' => 90000,
            'job_type' => 'Full-time',
            'location_type' => 'Remote',
            'description' => 'A test job listing for property-based testing.',
            'skills' => ['PHP', 'Laravel'],
            'status' => 'active',
        ]);
    }

    /**
     * Build a randomized but valid application payload.
     *
     * Required fields (name, email) are always present and non-empty. Nullable
     * fields (phone, cover_letter, additional_info) are randomly included so the
     * property covers both supplied and omitted values. All generated string
     * values have non-whitespace first/last characters so that the framework's
     * TrimStrings middleware leaves them unchanged, making exact-match assertions
     * deterministic.
     *
     * @return array<string, string>
     */
    private function randomValidPayload(): array
    {
        $payload = [
            'applicant_name' => $this->randomText(random_int(1, 255), false),
            'applicant_email' => fake()->unique()->safeEmail(),
        ];

        if (random_int(0, 1) === 1) {
            $payload['applicant_phone'] = $this->randomDigits(random_int(1, 20));
        }

        if (random_int(0, 1) === 1) {
            $payload['cover_letter'] = $this->randomText(random_int(1, 5000), true);
        }

        if (random_int(0, 1) === 1) {
            $payload['additional_info'] = $this->randomText(random_int(1, 5000), true);
        }

        return $payload;
    }

    /**
     * Generate a random string of the given length whose first and last
     * characters are never whitespace (so TrimStrings cannot alter it).
     */
    private function randomText(int $length, bool $multiline): string
    {
        $length = max(1, $length);

        $nonSpace = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.,-_';
        $whitespace = $multiline ? " \t\n" : ' ';
        $all = $nonSpace.$whitespace;

        if ($length === 1) {
            return $nonSpace[random_int(0, strlen($nonSpace) - 1)];
        }

        $chars = $nonSpace[random_int(0, strlen($nonSpace) - 1)];
        for ($i = 1; $i < $length - 1; $i++) {
            $chars .= $all[random_int(0, strlen($all) - 1)];
        }
        $chars .= $nonSpace[random_int(0, strlen($nonSpace) - 1)];

        return $chars;
    }

    /**
     * Generate a random string of digits of the given length.
     */
    private function randomDigits(int $length): string
    {
        $digits = '';
        for ($i = 0; $i < max(1, $length); $i++) {
            $digits .= (string) random_int(0, 9);
        }

        return $digits;
    }
}
