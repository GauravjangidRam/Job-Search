<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\BookmarkController;
use App\Models\Bookmark;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 19: Bookmark toggle idempotence
 *
 * For any user-job_listing pair, toggling the bookmark an EVEN number of times
 * results in NO bookmark existing, and toggling an ODD number of times results
 * in exactly ONE bookmark existing. No duplicate bookmark rows ever accumulate
 * for the same (user_id, job_listing_id) pair.
 *
 * BookmarkController::toggle(JobListing $job) creates a bookmark if none exists
 * for the (user_id, job_listing_id) pair, otherwise deletes the existing one.
 *
 * Validates: Requirements 13.1, 13.2
 */
class BookmarkControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Minimum number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 150;

    protected function setUp(): void
    {
        parent::setUp();

        // The toggle route is not registered in the application yet (task 17.1),
        // so register it here for the HTTP-based exercise of the controller.
        Route::middleware('web')
            ->post('/bookmarks/{job}/toggle', [BookmarkController::class, 'toggle'])
            ->name('bookmarks.toggle');

        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    /**
     * Create a fresh seeker user.
     */
    private function createSeeker(): User
    {
        return User::factory()->create(['role' => 'seeker']);
    }

    /**
     * Create a fresh active job listing with all required columns populated.
     */
    private function createJobListing(): JobListing
    {
        return JobListing::create([
            'title' => 'Senior PHP Engineer',
            'company_name' => 'Acme Corp',
            'location' => 'Remote',
            'salary_min' => 80000,
            'salary_max' => 120000,
            'job_type' => 'Full-time',
            'location_type' => 'Remote',
            'description' => 'A great job opportunity.',
            'skills' => ['PHP', 'Laravel'],
            'status' => 'active',
        ]);
    }

    /**
     * Toggle the bookmark via the HTTP route as the given seeker.
     */
    private function toggle(User $seeker, JobListing $job): void
    {
        $this->actingAs($seeker)
            ->post("/bookmarks/{$job->id}/toggle")
            ->assertRedirect();
    }

    /**
     * Count bookmark rows for a specific (user_id, job_listing_id) pair.
     */
    private function bookmarkCount(User $seeker, JobListing $job): int
    {
        return Bookmark::query()
            ->where('user_id', $seeker->id)
            ->where('job_listing_id', $job->id)
            ->count();
    }

    public function test_property_toggle_idempotence_even_clears_odd_keeps_single_bookmark(): void
    {
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // Generate a toggle count that guarantees a healthy mix of even and
            // odd values across iterations (including the zero edge case).
            $toggleCount = random_int(0, 20);

            $seeker = $this->createSeeker();
            $job = $this->createJobListing();

            for ($t = 0; $t < $toggleCount; $t++) {
                $this->toggle($seeker, $job);

                // After every individual toggle the row count is either 0 or 1 -
                // a bookmark is never duplicated for the same pair.
                $this->assertLessThanOrEqual(
                    1,
                    $this->bookmarkCount($seeker, $job),
                    sprintf(
                        'Bookmark rows exceeded 1 after %d of %d toggles (iteration %d).',
                        $t + 1,
                        $toggleCount,
                        $i
                    )
                );
            }

            $expected = $toggleCount % 2 === 0 ? 0 : 1;

            $this->assertSame(
                $expected,
                $this->bookmarkCount($seeker, $job),
                sprintf(
                    'Expected %d bookmark(s) after %d toggles (iteration %d) but found %d.',
                    $expected,
                    $toggleCount,
                    $i,
                    $this->bookmarkCount($seeker, $job)
                )
            );
        }
    }

    public function test_odd_number_of_toggles_leaves_exactly_one_bookmark(): void
    {
        $seeker = $this->createSeeker();
        $job = $this->createJobListing();

        foreach ([1, 3, 5, 7] as $toggles) {
            // Reset to a clean state for each odd count.
            Bookmark::query()->delete();

            for ($t = 0; $t < $toggles; $t++) {
                $this->toggle($seeker, $job);
            }

            $this->assertSame(1, $this->bookmarkCount($seeker, $job));
        }
    }

    public function test_even_number_of_toggles_leaves_no_bookmark(): void
    {
        $seeker = $this->createSeeker();
        $job = $this->createJobListing();

        foreach ([0, 2, 4, 6] as $toggles) {
            Bookmark::query()->delete();

            for ($t = 0; $t < $toggles; $t++) {
                $this->toggle($seeker, $job);
            }

            $this->assertSame(0, $this->bookmarkCount($seeker, $job));
        }
    }

    public function test_single_toggle_creates_and_second_toggle_removes_the_bookmark(): void
    {
        $seeker = $this->createSeeker();
        $job = $this->createJobListing();

        $this->toggle($seeker, $job);
        $this->assertSame(1, $this->bookmarkCount($seeker, $job));

        $this->toggle($seeker, $job);
        $this->assertSame(0, $this->bookmarkCount($seeker, $job));
    }
}
