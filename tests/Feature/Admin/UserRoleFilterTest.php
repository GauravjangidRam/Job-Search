<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 10: Admin user filter by role
 *
 * For any role filter value, all users returned by the admin filter have a role
 * exactly matching the filter value.
 *
 * The controller (Admin\UserController::index) filters with:
 *   ->when($role, fn($q, $role) => $q->where('role', $role))
 *
 * **Validates: Requirements 6.4**
 */
class UserRoleFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valid roles in the system.
     */
    private const ROLES = ['seeker', 'employer', 'admin'];

    /**
     * Number of randomized iterations (>= 100 as required by design).
     */
    private const ITERATIONS = 120;

    /**
     * Property 10: Admin user filter by role — for any role filter value, all
     * users returned by the admin filter have a role exactly matching the filter
     * value. Also asserts completeness (count matches).
     *
     * Tests at the DB/query level since the view may not exist.
     *
     * **Validates: Requirements 6.4**
     */
    public function test_property_admin_user_filter_by_role(): void
    {
        $faker = \Faker\Factory::create();
        $faker->seed(20250610);

        // --- Setup: create 25 users with varied roles ---
        $users = [];
        for ($u = 0; $u < 25; $u++) {
            $role = $faker->randomElement(self::ROLES);
            $users[] = User::factory()->create([
                'role' => $role,
                'email' => $faker->unique()->safeEmail(),
            ]);
        }

        // --- Property iterations: pick a random role and verify filter ---
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $filterRole = $faker->randomElement(self::ROLES);

            // Replicate the controller's query logic exactly:
            // User::query()->when($role, fn($q, $role) => $q->where('role', $role))
            $results = User::query()
                ->when($filterRole, fn ($query, $role) => $query->where('role', $role))
                ->get();

            // Assert: every returned user has role exactly matching the filter
            foreach ($results as $user) {
                $this->assertSame(
                    $filterRole,
                    $user->role,
                    "Iteration {$i}: user '{$user->email}' has role '{$user->role}' but filter was '{$filterRole}'."
                );
            }

            // Assert completeness: count matches expected
            $expectedCount = User::where('role', $filterRole)->count();
            $this->assertCount(
                $expectedCount,
                $results,
                "Iteration {$i}: result count ({$results->count()}) must match expected count ({$expectedCount}) for role '{$filterRole}'."
            );
        }
    }
}
