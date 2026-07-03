<?php

namespace Tests\Feature\Employer;

use App\Http\Controllers\Employer\JobListingController;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 15: New job listing defaults to draft
 *
 * For any valid job listing data submitted by an Employer, the created
 * JobListing record has status "draft" and company_id matching the Employer's
 * company.
 *
 * Employer\JobListingController::store(StoreJobListingRequest) takes the
 * validated job fields, then unconditionally overrides:
 *   - company_id   = Auth::user()->company_id
 *   - company_name = Auth::user()->company?->name
 *   - status       = 'draft'
 * before calling JobListing::create(...). Because the controller never reads a
 * client-supplied status, no valid (or even malicious) payload can change the
 * persisted status away from "draft" or point the listing at another company.
 *
 * **Validates: Requirements 11.2, 15.3**
 */
class JobManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations per property (design requires >= 100).
     */
    private const ITERATIONS = 120; 

    protected function setUp(): void
    {
        parent::setUp();

        // The employer job management routes are registered globally in a later
        // task (17.1). Register the index + store routes here so the controller
        // and form request can be exercised end-to-end in isolation, mirroring
        // the other employer feature tests.
        Route::middleware('web')->group(function () {
            Route::get('/employer/jobs', [JobListingController::class, 'index'])
                ->name('employer.jobs.index');
            Route::post('/employer/jobs', [JobListingController::class, 'store'])
                ->name('employer.jobs.store');
        });

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();
    } 

    /**
     * Create a company plus an employer User linked to it via company_id.
     *
     * @return array{0: User, 1: Company}
     */
    private function employerWithCompany(\Faker\Generator $faker): array
    {
        $company = Company::create([
            'name' => $faker->unique()->company(),
            'industry' => substr($faker->jobTitle(), 0, 100),
            'description' => substr($faker->paragraph(2), 0, 5000),
        ]);

        $user = User::factory()->create([
            'role' => 'employer',
            'company_id' => $company->id,
        ]);

        return [$user, $company];
    }

    /**
     * Build a randomized but valid store payload within the
     * StoreJobListingRequest validation bounds.
     *
     * @return array<string, mixed>
     */
    private function validStorePayload(\Faker\Generator $faker): array
    {
        $salaryMin = $faker->numberBetween(0, 200000);
        $salaryMax = $salaryMin + $faker->numberBetween(0, 100000);
        // skills: usually a list of strings, occasionally omitted (nullable).
        $skills = $faker->boolean(80)
            ? $faker->randomElements(
                ['PHP', 'Laravel', 'Vue', 'React', 'SQL', 'AWS', 'Docker', 'Go', 'Python', 'TypeScript'],
                $faker->numberBetween(1, 5)
            )
            : null;

        return [
            'title' => substr($faker->jobTitle(), 0, 255),
            'description' => $faker->paragraph(),
            'location' => substr($faker->city(), 0, 255),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'job_type' => $faker->randomElement(['Full-time', 'Part-time', 'Contract', 'Internship']),
            'location_type' => $faker->randomElement(['Remote', 'On-site', 'Hybrid']),
            'skills' => $skills,
        ];
    } 

    /**
     * Property 15: New job listing defaults to draft.
     *
     * For randomized valid store payloads submitted by an employer, the created
     * JobListing always persists with status "draft" and company_id matching
     * the authenticated employer's company.
     *
     * **Validates: Requirements 11.2, 15.3**
     */
    public function test_property_new_job_listing_defaults_to_draft(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250115);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            [$employer, $company] = $this->employerWithCompany($faker);
            $payload = $this->validStorePayload($faker);

            $existingIds = JobListing::where('company_id', $company->id)->pluck('id')->all();

            $response = $this->actingAs($employer)->post('/employer/jobs', $payload);

            // A validation failure would skip creation entirely; surface it.
            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('employer.jobs.index'));
            // Load the newly created listing (the one not present before).
            $listing = JobListing::where('company_id', $company->id)
                ->whereNotIn('id', $existingIds)
                ->latest('id')
                ->firstOrFail();

            $this->assertSame(
                'draft',
                $listing->status,
                sprintf('Iteration %d: a newly created listing must default to status "draft".', $i)
            );

            $this->assertSame(
                $company->id,
                $listing->company_id,
                sprintf("Iteration %d: a newly created listing's company_id must match the employer's company.", $i)
            );
        } 
    }

    /**
     * Property 15 (status cannot be overridden): even when the payload smuggles
     * a non-draft status, the controller forces the persisted status to "draft".
     *
     * **Validates: Requirements 11.2, 15.3**
     */
    public function test_property_submitted_status_cannot_override_draft_default(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(778899);

        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            [$employer, $company] = $this->employerWithCompany($faker);

            $payload = $this->validStorePayload($faker);
            // Attempt to smuggle in an alternate status.
            $payload['status'] = $faker->randomElement(['active', 'closed', 'published', 'open']);

            $existingIds = JobListing::where('company_id', $company->id)->pluck('id')->all();

            $response = $this->actingAs($employer)->post('/employer/jobs', $payload);

            $response->assertSessionHasNoErrors();

            $listing = JobListing::where('company_id', $company->id)
                ->whereNotIn('id', $existingIds)
                ->latest('id')
                ->firstOrFail();

            $this->assertSame(
                'draft',
                $listing->status,
                sprintf('Iteration %d: a smuggled status must not override the "draft" default.', $i)
            );
            $this->assertSame($company->id, $listing->company_id);
        }
    }

    /**
     * Example: a single straightforward submission creates a draft listing
     * scoped to the employer's company.
     *
     * **Validates: Requirements 11.2, 15.3**
     */
    public function test_example_single_submission_creates_draft_listing(): void
    {
        $faker = \Faker\Factory::create();

        [$employer, $company] = $this->employerWithCompany($faker);

        $response = $this->actingAs($employer)->post('/employer/jobs', [
            'title' => 'Senior Laravel Engineer',
            'description' => 'Build and maintain platform features.',
            'location' => 'Remote',
            'salary_min' => 90000,
            'salary_max' => 140000,
            'job_type' => 'Full-time',
            'location_type' => 'Remote',
            'skills' => ['PHP', 'Laravel'],
        ]);

        $response->assertRedirect(route('employer.jobs.index'));
        $response->assertSessionHasNoErrors();

        $listing = JobListing::where('company_id', $company->id)->sole();

        $this->assertSame('draft', $listing->status);
        $this->assertSame($company->id, $listing->company_id);
        $this->assertSame($company->name, $listing->company_name);
    }
}
