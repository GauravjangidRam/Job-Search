<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RedirectIfAuthenticated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_path_for_seeker(): void
    {
        $user = User::factory()->make(['role' => 'seeker']);

        $this->assertSame('/profile', RedirectIfAuthenticated::redirectPathForUser($user));
    }

    public function test_redirect_path_for_employer(): void
    {
        $user = User::factory()->make(['role' => 'employer']);

        $this->assertSame('/employer/dashboard', RedirectIfAuthenticated::redirectPathForUser($user));
    }

    public function test_redirect_path_for_admin(): void
    {
        $user = User::factory()->make(['role' => 'admin']);

        $this->assertSame('/admin/dashboard', RedirectIfAuthenticated::redirectPathForUser($user));
    }

    public function test_redirect_path_falls_back_to_jobs_for_unknown_role(): void
    {
        $user = User::factory()->make(['role' => 'unknown']);

        $this->assertSame('/jobs', RedirectIfAuthenticated::redirectPathForUser($user));
    }

    public function test_redirect_path_falls_back_to_jobs_for_null_user(): void
    {
        $this->assertSame('/jobs', RedirectIfAuthenticated::redirectPathForUser(null));
    }

    public function test_guest_request_passes_through(): void
    {
        $middleware = new RedirectIfAuthenticated();
        $request = Request::create('/login', 'GET');
        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;
            return response('ok');
        });

        $this->assertTrue($called);
        $this->assertSame('ok', $response->getContent());
    }

    public function test_authenticated_seeker_is_redirected_to_profile(): void
    {
        $user = User::factory()->create(['role' => 'seeker']);
        Auth::login($user);

        $middleware = new RedirectIfAuthenticated();
        $request = Request::create('/login', 'GET');

        $called = false;
        $response = $middleware->handle($request, function () use (&$called) {
            $called = true;

            return response('ok');
        });

        $this->assertFalse($called);
        $this->assertTrue($response->isRedirect());
        $this->assertSame(url('/profile'), $response->headers->get('Location'));
    }

    public function test_authenticated_employer_is_redirected_to_employer_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'employer']);
        Auth::login($user);

        $middleware = new RedirectIfAuthenticated();
        $request = Request::create('/register', 'GET');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(url('/employer/dashboard'), $response->headers->get('Location'));
    }

    public function test_authenticated_admin_is_redirected_to_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Auth::login($user);

        $middleware = new RedirectIfAuthenticated();
        $request = Request::create('/login', 'GET');

        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertTrue($response->isRedirect());
        $this->assertSame(url('/admin/dashboard'), $response->headers->get('Location'));
    }
}
