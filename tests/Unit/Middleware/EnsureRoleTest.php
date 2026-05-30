<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 1: Role-based access control enforcement
 *
 * For any authenticated user with role R and any route protected for the set of
 * roles S where R is not in S, the EnsureRole middleware SHALL return a 403
 * Forbidden response and deny access to the route's controller action. When R is
 * in S, the request SHALL pass through to the next handler.
 *
 * Validates: Requirements 1.4, 1.5, 9.5
 */
class EnsureRoleTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private const ROLES = ['seeker', 'employer', 'admin'];

    private const ITERATIONS = 150;

    /**
     * Invoke the middleware and report whether $next was reached or a 403 was
     * raised. abort(403) throws a Symfony HttpException carrying status 403.
     *
     * @param  list<string>  $requiredRoles
     * @return array{passed: bool, status: int|null}
     */
    private function runMiddleware(array $requiredRoles): array
    {
        $middleware = new EnsureRole();
        $request = Request::create('/protected', 'GET');
        $nextCalled = false;

        try {
            $response = $middleware->handle($request, function () use (&$nextCalled) {
                $nextCalled = true;

                return response('ok');
            }, ...$requiredRoles);

            return ['passed' => $nextCalled, 'status' => $response->getStatusCode()];
        } catch (HttpException $e) {
            return ['passed' => false, 'status' => $e->getStatusCode()];
        }
    }

    /**
     * Produce a random non-empty subset of the given roles. This represents the
     * set of roles a protected route is restricted to.
     *
     * @param  list<string>  $roles
     * @return list<string>
     */
    private function randomNonEmptySubset(array $roles): array
    {
        do {
            $subset = array_values(array_filter($roles, fn () => (bool) random_int(0, 1)));
        } while ($subset === []);

        return $subset;
    }

    public function test_property_role_based_access_control_enforcement(): void
    {
        $deniedCount = 0;
        $allowedCount = 0;

        for ($i = 0; $i < self::ITERATIONS; $i++) {
            // Generate a randomized authenticated user role.
            $userRole = self::ROLES[array_rand(self::ROLES)];

            // Generate a randomized non-empty set of roles the route requires.
            $requiredRoles = $this->randomNonEmptySubset(self::ROLES);

            $user = User::factory()->create(['role' => $userRole]);
            Auth::login($user);

            $result = $this->runMiddleware($requiredRoles);

            $context = "user role [{$userRole}] vs required roles [".implode(',', $requiredRoles).']';

            if (in_array($userRole, $requiredRoles, true)) {
                // R is in S: access granted, $next invoked, response passes through.
                $allowedCount++;
                $this->assertTrue(
                    $result['passed'],
                    "Expected access to be granted for {$context}, but it was denied."
                );
                $this->assertSame(200, $result['status'], "Expected pass-through 200 for {$context}.");
            } else {
                // R is not in S: middleware aborts with 403 and denies access.
                $deniedCount++;
                $this->assertFalse(
                    $result['passed'],
                    "Expected access to be denied for {$context}, but it was granted."
                );
                $this->assertSame(403, $result['status'], "Expected 403 Forbidden for {$context}.");
            }

            Auth::logout();
        }

        // Confirm the randomized run actually exercised both branches.
        $this->assertGreaterThan(0, $deniedCount, 'Expected at least one role-mismatch (denied) case.');
        $this->assertGreaterThan(0, $allowedCount, 'Expected at least one role-match (allowed) case.');
    }

    /**
     * Exhaustively verify every (userRole, requiredRole) single-role pairing so
     * that all 6 mismatch combinations and all 3 match combinations are covered
     * deterministically in addition to the randomized run above.
     */
    public function test_all_single_role_combinations_enforced(): void
    {
        foreach (self::ROLES as $userRole) {
            foreach (self::ROLES as $requiredRole) {
                $user = User::factory()->create(['role' => $userRole]);
                Auth::login($user);

                $result = $this->runMiddleware([$requiredRole]);

                if ($userRole === $requiredRole) {
                    $this->assertTrue($result['passed'], "[{$userRole}] should access route requiring [{$requiredRole}].");
                    $this->assertSame(200, $result['status']);
                } else {
                    $this->assertFalse($result['passed'], "[{$userRole}] must NOT access route requiring [{$requiredRole}].");
                    $this->assertSame(403, $result['status']);
                }

                Auth::logout();
            }
        }
    }

    /**
     * An unauthenticated (guest) request must always be denied with 403,
     * regardless of which roles the route requires.
     */
    public function test_unauthenticated_request_is_denied(): void
    {
        $this->assertGuest();

        $result = $this->runMiddleware(['seeker', 'employer', 'admin']);

        $this->assertFalse($result['passed']);
        $this->assertSame(403, $result['status']);
    }
}
