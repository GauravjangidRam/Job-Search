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
 * Feature: full-platform-features, Property 7: Duplicate application prevention
 *
 * For any user-job_listing pair that already has a job_application record,
 * attempting to create a second application for the same pair is rejected and
 * the total application count for that pair remains exactly 1.
 *
 * JobController::submitApplication() looks up an existing JobApplication for
 * (user_id, job_listing_id) and, if one is present, redirects back with a
 * flash 'error' message without creating another record.
 *
 * The job application routes are registered globally in a later task (17.1);
 * they are registered here in setUp() so the controller, form request, and
 * service can be exercised end-to-end in isolation.
 *
 * **Validates: Requirements 4.5**
 */
class DuplicateApplicationTest extends TestCase
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
        // the duplicate-prevention behavior can be exercised in isolation.
        Route::middleware('web')->post('/jobs/{hash}/apply', [JobController::class, 'submitApplication'])
            ->name('jobs.submitApplication');
        Route::middleware('web')->get('/jobs/{hash}/apply', [JobController::class, 'apply'])
            ->name('jobs.apply');

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();

        // Resumes are stored on the "local" disk by FileUploadService.
        Storage::fake('local');

        // Avoid dispatching real mail when an application is created.
        Mail::fake();
    }

    /**
     * Create a freshly-persisted Seeker.
     */
    private function createSeeker(int $index): User
    {
        return User::factory()->create([
            'role' => 'seeker',
            'email' => "seeker{$index}@example.com",
        ]);
    }

    /**
     * Create an active JobListing with all required columns populated.
     */
    private function createActiveListing(\Faker\Generator $faker): JobListing
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
        ]);
    }

    /**
     * Count the applications belonging to a specific (user, listing) pair.
     */
    private function pairApplicationCount(int $userId, int $jobListingId): int
    {
        return JobApplication::where('user_id', $userId)
            ->where('job_listing_id', $jobListingId)
            ->count();
    }

    /**
     * Build a valid application payload with a fake PDF resume.
     *
     * @return array<string, mixed>
     */
    private function validPayload(User $user): array
    {
        return [
            'applicant_name' => $user->name,
            'applicant_email' => $user->email,
            'applicant_phone' => '5551234567',
            'resume' => UploadedFile::fake()->create('r.pdf', 100, 'application/pdf'),
            'cover_letter' => 'I am very interested in this role.',
            'additional_info' => 'Available immediately.',
        ];
    }

    /**
     * Property 7: Duplicate application prevention.
     *
     * For a fresh seeker + active listing, the first valid submission creates
     * exactly one application. Each subsequent attempt for the same pair (a
     * randomized 1..5 additional attempts) is rejected with a flash 'error' and
     * leaves the pair's application count at exactly 1.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_duplicate_application_is_prevented_and_count_stays_one(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250105);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $user = $this->createSeeker($i);
            $listing = $this->createActiveListing($faker);

            // First submission: a valid application is created for the pair.
            $firstResponse = $this->actingAs($user)
                ->post("/jobs/{$listing->hashed_id}/apply", $this->validPayload($user));

            $firstResponse->assertSessionHas('success');
            $this->assertSame(
                1,
                $this->pairApplicationCount($user->id, $listing->id),
                "Iteration {$i}: first submission should create exactly one application."
            );

            // A randomized number of additional attempts for the same pair, all
            // of which must be rejected while the pair count stays at 1.
            $extraAttempts = random_int(1, 5);
            for ($attempt = 0; $attempt < $extraAttempts; $attempt++) {
                $response = $this->actingAs($user)
                    ->post("/jobs/{$listing->hashed_id}/apply", $this->validPayload($user));

                $response->assertSessionHas('error');

                $this->assertSame(
                    1,
                    $this->pairApplicationCount($user->id, $listing->id),
                    sprintf(
                        'Iteration %d, attempt %d: duplicate submission must not increase the pair count.',
                        $i,
                        $attempt + 1
                    )
                );
            }
        }
    }

    /**
     * Example: a single duplicate attempt redirects back to the apply page with
     * the flash 'error' message and creates no second record.
     *
     * **Validates: Requirements 4.5**
     */
    public function test_single_duplicate_attempt_redirects_with_error(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(424242);

        $user = $this->createSeeker(9001);
        $listing = $this->createActiveListing($faker);

        $this->actingAs($user)
            ->post("/jobs/{$listing->hashed_id}/apply", $this->validPayload($user))
            ->assertSessionHas('success');

        $response = $this->actingAs($user)
            ->post("/jobs/{$listing->hashed_id}/apply", $this->validPayload($user));

        $response->assertRedirect(route('jobs.apply', ['hash' => $listing->hashed_id]));
        $response->assertSessionHas('error');

        $this->assertSame(1, $this->pairApplicationCount($user->id, $listing->id));
    }

    /**
     * Example: a different seeker applying to the same listing is NOT treated
     * as a duplicate; duplicate prevention is scoped to the (user, listing)
     * pair, not the listing alone.
     */
    public function test_different_seeker_can_apply_to_same_listing(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(7);

        $listing = $this->createActiveListing($faker);

        $userA = $this->createSeeker(8001);
        $userB = $this->createSeeker(8002);

        $this->actingAs($userA)
            ->post("/jobs/{$listing->hashed_id}/apply", $this->validPayload($userA))
            ->assertSessionHas('success');

        $this->actingAs($userB)
            ->post("/jobs/{$listing->hashed_id}/apply", $this->validPayload($userB))
            ->assertSessionHas('success');

        $this->assertSame(1, $this->pairApplicationCount($userA->id, $listing->id));
        $this->assertSame(1, $this->pairApplicationCount($userB->id, $listing->id));
        $this->assertSame(2, JobApplication::where('job_listing_id', $listing->id)->count());
    }
}
