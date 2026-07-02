<?php

namespace Tests\Feature\Profile;

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 2: Profile update round-trip
 *
 * For any valid profile data (name, phone, bio, avatar), when a Seeker submits
 * the profile update form, querying the user record back from the database
 * returns the same submitted values. The email address is immutable and must
 * never change, even if an 'email' field is included in the payload.
 *
 * ProfileController::update() validates via ProfileUpdateRequest, persists
 * name/phone/bio, and - when an avatar is uploaded - stores it through the
 * FileUploadService (local disk) and saves the returned avatar_path. It never
 * touches the email column.
 *
 * The profile routes are registered globally in a later task (17.1); they are
 * registered here in setUp() so the controller, form request, and service can
 * be exercised end-to-end in isolation.
 *
 * **Validates: Requirements 2.2**
 */
class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /** 
     * Number of randomized iterations for the property (design requires >= 100).
     */
    private const ITERATIONS = 120;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->group(function () {
            Route::put('/profile', [ProfileController::class, 'update'])
                ->name('profile.update');
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        }); 

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();

        // Avatars are stored on the "local" disk by FileUploadService.
        Storage::fake('local');
    }

    /**
     * Create a freshly-persisted Seeker with a known email.
     */
    private function createSeeker(string $email): User
    {
        return User::factory()->create([
            'role' => 'seeker',
            'email' => $email,
        ]);
    }

    /**
     * Generate a phone string guaranteed to be within the 20-character limit
     * and free of surrounding whitespace (so TrimStrings cannot alter it).
     */
    private function randomPhone(\Faker\Generator $faker): string
    {
        return $faker->numerify(str_repeat('#', random_int(7, 15)));
    }

    /**
     * Generate a bio within the 5000-character limit, trimmed so the value the
     * controller stores matches exactly what was submitted (TrimStrings runs in
     * the web middleware group).
     */
    private function randomBio(\Faker\Generator $faker): string
    {
        return trim($faker->text(random_int(10, 4000)));
    }

    /**
     * Generate a name within the 255-character limit and free of surrounding
     * whitespace.
     */
    private function randomName(\Faker\Generator $faker): string
    {
        return trim($faker->name());
    }

    /**
     * Property 2: Profile update round-trip.
     *
     * For randomized valid (name, phone, bio) values - and on a subset of
     * iterations, an avatar upload - submitting the profile update form and
     * reloading the user from the database yields exactly the submitted values.
     * The email is immutable even when supplied in the payload.
     *
     * **Validates: Requirements 2.2**
     */
    public function test_profile_update_round_trip_persists_submitted_values(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250105);

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $email = "seeker{$i}@example.com";
            $user = $this->createSeeker($email);

            $name = $this->randomName($faker);
            $phone = $this->randomPhone($faker);
            $bio = $this->randomBio($faker);

            // Include an 'email' field in the payload to assert immutability.
            $payload = [
                'name' => $name,
                'phone' => $phone,
                'bio' => $bio,
                'email' => "attacker{$i}@example.com",
            ];

            // Exercise the avatar branch on roughly every third iteration.
            $withAvatar = ($i % 3 === 0);
            if ($withAvatar) {
                $payload['avatar'] = UploadedFile::fake()->image('a.png');
            }

            $response = $this->actingAs($user)->put('/profile', $payload);

            // A failed validation would silently skip the update; surface it.
            $response->assertSessionHasNoErrors();
            $response->assertRedirect(route('profile.show'));

            $fresh = $user->fresh();

            $this->assertSame(
                $name,
                $fresh->name,
                "Iteration {$i}: name did not round-trip."
            );
            $this->assertSame(
                $phone,
                $fresh->phone,
                "Iteration {$i}: phone did not round-trip."
            );
            $this->assertSame(
                $bio,
                $fresh->bio,
                "Iteration {$i}: bio did not round-trip."
            );

            // Requirement 2.5: email is immutable.
            $this->assertSame(
                $email,
                $fresh->email,
                "Iteration {$i}: email must not change."
            );

            if ($withAvatar) {
                $this->assertNotNull(
                    $fresh->avatar_path,
                    "Iteration {$i}: avatar_path should be set after an upload."
                );
                Storage::disk('local')->assertExists($fresh->avatar_path);
            }
        }
    }

    /**
     * Boundary example: maximum-length values for name (255), phone (20), and
     * bio (5000) round-trip intact.
     */
    public function test_profile_update_round_trip_at_max_lengths(): void
    {
        $user = $this->createSeeker('boundary@example.com');

        $name = str_repeat('a', 255);
        $phone = str_repeat('1', 20);
        $bio = str_repeat('b', 5000);

        $response = $this->actingAs($user)->put('/profile', [
            'name' => $name,
            'phone' => $phone,
            'bio' => $bio,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.show'));

        $fresh = $user->fresh();
        $this->assertSame($name, $fresh->name);
        $this->assertSame($phone, $fresh->phone);
        $this->assertSame($bio, $fresh->bio);
    }

    /**
     * Example: an uploaded avatar is stored on the faked local disk and its
     * path is saved to the user record.
     */
    public function test_avatar_upload_is_stored_and_path_saved(): void
    {
        $user = $this->createSeeker('avatar@example.com');

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'Avatar User',
            'phone' => '5551234567',
            'bio' => 'Has an avatar.',
            'avatar' => UploadedFile::fake()->image('me.png', 200, 200),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.show'));

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->avatar_path);
        Storage::disk('local')->assertExists($fresh->avatar_path);
        $this->assertStringStartsWith('avatars/', $fresh->avatar_path);
    }

    /**
     * Example: submitting an 'email' field never changes the immutable email.
     *
     * **Validates: Requirements 2.5**
     */
    public function test_email_is_immutable_even_when_supplied(): void
    {
        $user = $this->createSeeker('original@example.com');

        $response = $this->actingAs($user)->put('/profile', [
            'name' => 'New Name',
            'phone' => '5550001111',
            'bio' => 'Trying to change email.',
            'email' => 'hacked@example.com',
        ]);

        $response->assertSessionHasNoErrors();

        $fresh = $user->fresh();
        $this->assertSame('original@example.com', $fresh->email);
        $this->assertSame('New Name', $fresh->name);
    }
}
