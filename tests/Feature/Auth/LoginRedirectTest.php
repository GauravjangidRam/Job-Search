<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 4: Intended URL preservation round-trip
 *
 * For any protected URL an unauthenticated user attempts to access, after
 * completing the full authentication flow (login + OTP verification), the
 * system redirects to that exact URL. When no intended URL was stored, the
 * flow falls back to the default post-login route (/jobs).
 *
 * AuthController::login() and AuthController::verifyOtp() both finish with
 * redirect()->intended('/jobs'), which pulls the 'url.intended' session key
 * that Laravel's Authenticate middleware populates on a guest redirect.
 */
class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property 4: Intended URL preservation round-trip.
     *
     * For randomized protected paths, seeding the OTP-flow session
     * ('otp_user_id' + the middleware-stored 'url.intended') and verifying a
     * valid OTP redirects to that exact intended URL.
     *
     * **Validates: Requirements 3.1, 3.2**
     */
    #[DataProvider('intendedUrlProvider')]
    public function test_intended_url_preserved_through_otp_verification(string $intendedUrl): void
    {
        $user = User::factory()->unverified()->create();

        // OtpService::generate() returns the plain-text OTP and stores a fresh,
        // non-expired hashed OTP with attempts reset to 0.
        $otp = app(OtpService::class)->generate($user);

        // The Authenticate middleware stores the absolute intended URL via the
        // 'url.intended' session key when redirecting a guest to login. The OTP
        // flow carries 'otp_user_id' through to verification.
        $response = $this->withSession([
            'otp_user_id' => $user->id,
            'url.intended' => url($intendedUrl),
        ])->post('/verify-otp', ['otp' => $otp]);

        // The full auth flow must land the user on the exact intended URL.
        $response->assertRedirect($intendedUrl);
        $this->assertAuthenticated();
    }

    /**
     * Full round-trip across both auth steps: a guest hits a protected route
     * (intended URL stored by middleware), submits the login form as an
     * unverified user (routed to OTP entry), then verifies the OTP. The
     * intended URL is preserved through the entire flow.
     *
     * **Validates: Requirements 3.1, 3.2, 3.5**
     */
    public function test_full_login_then_otp_flow_preserves_intended_url(): void
    {
        $intended = '/employer/dashboard';

        $user = User::factory()->unverified()->create([
            'email' => 'flow@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Step 1: guest redirect populates 'url.intended', then login as an
        // unverified user routes to OTP verification without clearing it.
        $loginResponse = $this->withSession(['url.intended' => url($intended)])
            ->post('/login', [
                'email' => 'flow@example.com',
                'password' => 'password123',
            ]);
        $loginResponse->assertRedirect('/verify-otp');
        $this->assertEquals($user->id, session('otp_user_id'));
        $this->assertEquals(url($intended), session('url.intended'));

        // Step 2: forward the session state the login step preserved, set a
        // known OTP, and complete verification.
        $otp = app(OtpService::class)->generate($user->fresh());

        $verifyResponse = $this->withSession([
            'otp_user_id' => session('otp_user_id'),
            'url.intended' => session('url.intended'),
        ])->post('/verify-otp', ['otp' => $otp]);

        $verifyResponse->assertRedirect($intended);
        $this->assertAuthenticated();
    }

    /**
     * A verified user logging in directly is redirected to the intended URL
     * stored by the middleware (the login step performs the intended redirect).
     *
     * **Validates: Requirements 3.1, 3.2**
     */
    public function test_verified_user_login_redirects_to_intended_url(): void
    {
        $intended = '/bookmarks';
        User::factory()->create([
            'email' => 'verified@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->withSession(['url.intended' => url($intended)])
            ->post('/login', [
                'email' => 'verified@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect($intended);
        $this->assertAuthenticated();
    }

    /**
     * Fallback: when no intended URL is stored, the auth flow redirects to the
     * default post-login route (/jobs).
     *
     * **Validates: Requirements 3.3, 3.4**
     */
    public function test_falls_back_to_jobs_when_no_intended_url(): void
    {
        $user = User::factory()->unverified()->create();
        $otp = app(OtpService::class)->generate($user);

        $response = $this->withSession(['otp_user_id' => $user->id])
            ->post('/verify-otp', ['otp' => $otp]);
        $response->assertRedirect('/jobs');
        $this->assertAuthenticated();
    }

    /**
     * Generates 100+ randomized, realistic protected relative paths (the kind
     * of URLs a guest might be redirected away from), including some with
     * numeric ids and query strings, to exercise the round-trip property
     * across a wide input space.
     *
     * @return array<string, array{string}>
     */
    public static function intendedUrlProvider(): array
    {
        // Seed for reproducibility so any counterexample is repeatable.
        mt_srand(20250105);
        $bases = [
            'profile',
            'bookmarks',
            'jobs',
            'employer/dashboard',
            'employer/jobs',
            'employer/applications',
            'employer/company',
            'admin/dashboard',
            'admin/users',
            'admin/companies',
            'companies',
            'insights',
            'resume',
        ];

        $cases = [];
        for ($i = 0; $i < 100; $i++) {
            $base = $bases[$i % count($bases)];
            $path = '/' . $base;

            $shape = mt_rand(0, 3);
            if ($shape === 1) {
                // Append a numeric resource id, e.g. /jobs/4218
                $path .= '/' . mt_rand(1, 99999);
            } elseif ($shape === 2) {
                // Append a query string, e.g. /jobs?page=7
                $path .= '?page=' . mt_rand(1, 50);
            } elseif ($shape === 3) {
                // Append id + query string, e.g. /jobs/120/apply?ref=42
                $path .= '/' . mt_rand(1, 99999) . '/apply?ref=' . mt_rand(1, 999);
            }

            $cases["case {$i}: {$path}"] = [$path];
        }

        return $cases;
    }
}
