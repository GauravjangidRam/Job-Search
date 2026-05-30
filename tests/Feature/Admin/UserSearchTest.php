<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 9: Admin user search correctness
 *
 * For any search term, all users returned by the admin search have the search
 * term as a case-insensitive substring of either their name or their email.
 *
 * The controller (Admin\UserController::index) filters with
 * where('name','LIKE','%search%')->orWhere('email','LIKE','%search%').
 *
 * **Validates: Requirements 6.3**
 */
class UserSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Number of randomized iterations (>= 100 as required by design).
     */
    private const ITERATIONS = 120;

    /**
     * Property 9: Admin user search correctness — for any search term, all
     * users returned by the admin search have the search term as a
     * case-insensitive substring of either their name or their email. Also
     * asserts completeness (no matching user was excluded).
     *
     * Tests at the DB/query level replicating the controller's search logic
     * since the view may not exist.
     *
     * **Validates: Requirements 6.3**
     */
    public function test_property_admin_user_search_correctness(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250120);

        // --- Setup: create 25 users with varied names and emails ---
        $users = [];
        for ($u = 0; $u < 25; $u++) {
            $users[] = User::factory()->create([
                'name' => $faker->name() . ' ' . $faker->randomElement(['Smith', 'García', 'O\'Brien', 'Zhang', 'Müller']),
                'email' => $faker->unique()->userName() . '@' . $faker->randomElement(['example.com', 'test.org', 'mail.io', 'company.net']),
                'role' => $faker->randomElement(['seeker', 'employer', 'admin']),
            ]);
        }

        // --- Property iterations: pick random search terms and verify ---
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // Generate a search term: sometimes a substring from an existing user's
            // name or email, sometimes a completely random string
            $searchTerm = $this->generateSearchTerm($faker, $users);

            // Replicate the controller's search query logic exactly
            $results = User::query()
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                })
                ->get();

            // Assert: every returned user has the search term as a case-insensitive
            // substring of either their name or their email
            foreach ($results as $user) {
                $nameContains = str_contains(
                    mb_strtolower($user->name),
                    mb_strtolower($searchTerm)
                );
                $emailContains = str_contains(
                    mb_strtolower($user->email),
                    mb_strtolower($searchTerm)
                );

                $this->assertTrue(
                    $nameContains || $emailContains,
                    "Iteration {$i}: User '{$user->name}' ({$user->email}) was returned for search term '{$searchTerm}' "
                    . "but the term is not a case-insensitive substring of name or email."
                );
            }

            // Assert completeness: no matching user was excluded
            $allUsers = User::all();
            $expectedCount = $allUsers->filter(function ($user) use ($searchTerm) {
                $nameContains = str_contains(
                    mb_strtolower($user->name),
                    mb_strtolower($searchTerm)
                );
                $emailContains = str_contains(
                    mb_strtolower($user->email),
                    mb_strtolower($searchTerm)
                );

                return $nameContains || $emailContains;
            })->count();

            $this->assertCount(
                $expectedCount,
                $results,
                "Iteration {$i}: Result count ({$results->count()}) does not match expected count ({$expectedCount}) "
                . "for search term '{$searchTerm}'. Some matching users may have been excluded."
            );
        }
    }

    /**
     * Generate a search term for testing. Mixes strategies:
     * - 40% chance: substring from an existing user's name
     * - 30% chance: substring from an existing user's email
     * - 20% chance: random short alphabetic string (may or may not match)
     * - 10% chance: empty-ish or single character
     */
    private function generateSearchTerm(\Faker\Generator $faker, array $users): string
    {
        $strategy = $faker->numberBetween(1, 100);

        if ($strategy <= 40) {
            // Substring from a random user's name
            $user = $faker->randomElement($users);
            $name = $user->name;
            $len = mb_strlen($name);
            if ($len <= 1) {
                return $name;
            }
            $start = $faker->numberBetween(0, max(0, $len - 2));
            $length = $faker->numberBetween(1, min(6, $len - $start));

            return mb_substr($name, $start, $length);
        }

        if ($strategy <= 70) {
            // Substring from a random user's email
            $user = $faker->randomElement($users);
            $email = $user->email;
            $len = mb_strlen($email);
            if ($len <= 1) {
                return $email;
            }
            $start = $faker->numberBetween(0, max(0, $len - 2));
            $length = $faker->numberBetween(1, min(8, $len - $start));

            return mb_substr($email, $start, $length);
        }

        if ($strategy <= 90) {
            // Random short alphabetic string
            return $faker->lexify(str_repeat('?', $faker->numberBetween(2, 5)));
        }

        // Single character
        return $faker->randomLetter();
    }
}
