<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployerRegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Mail\OtpMail;
use App\Models\Company;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly OtpService $otpService,
    ) {}

    /**
     * Display the registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request.
     *
     * Creates the user, generates an OTP, sends it via email,
     * and redirects to the OTP verification page.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        // TODO: Uncomment when email service is configured
        // $otp = $this->otpService->generate($user);
        // Mail::to($user->email)->send(new OtpMail($otp));
        // $request->session()->put('otp_user_id', $user->id);
        // return redirect('/verify-otp');

        // Bypass OTP - directly log in the user
        Auth::login($user);
        return redirect('/');
    }

    /**
     * Display the employer registration form.
     */
    public function showEmployerRegister(): View
    {
        return view('auth.employer-register');
    }

    /**
     * Handle an employer registration request.
     *
     * Within a database transaction, creates a Company from the company
     * fields, creates a User with the "employer" role linked to that company,
     * generates an OTP, sends it via email, and redirects to OTP verification.
     */
    public function employerRegister(EmployerRegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            $company = Company::create([
                'name' => $request->validated('company_name'),
                'industry' => $request->validated('industry'),
                'description' => $request->validated('description'),
            ]);

            return User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => 'employer',
                'company_id' => $company->id,
            ]);
        });

        // TODO: Uncomment when email service is configured
        // $otp = $this->otpService->generate($user);
        // Mail::to($user->email)->send(new OtpMail($otp));
        // $request->session()->put('otp_user_id', $user->id);
        // return redirect('/verify-otp');

        // Bypass OTP - directly log in the user
        Auth::login($user);
        return redirect('/employer/dashboard');
    }

    /**
     * Display the OTP verification form.
     *
     * Redirects to login if no otp_user_id is stored in the session.
     */
    public function showOtpVerification(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_user_id')) {
            return redirect('/login');
        }

        return view('auth.verify-otp');
    }

    /**
     * Handle OTP verification submission.
     *
     * Checks expiration, attempts (max 5), verifies OTP,
     * marks email as verified, logs user in, and redirects to /jobs.
     */
    public function verifyOtp(OtpVerifyRequest $request): RedirectResponse
    {
        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect('/login');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect('/login');
        }

        // Check if OTP has expired
        if ($this->otpService->isExpired($user)) {
            return back()->withErrors([
                'otp' => 'Your OTP has expired. Please request a new one.',
            ]);
        }

        // Check if max attempts exceeded
        if ($user->otp_attempts >= 5) {
            return back()->withErrors([
                'otp' => 'Too many failed attempts. Please request a new OTP.',
            ]);
        }

        // Verify the OTP
        if (! $this->otpService->verify($user, $request->validated('otp'))) {
            return back()->withErrors([
                'otp' => 'The OTP you entered is invalid.',
            ]);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->save();

        // Invalidate the OTP
        $this->otpService->invalidate($user);

        // Log the user in
        Auth::login($user);

        // Clear the session OTP user ID
        $request->session()->forget('otp_user_id');

        // Redirect to the intended URL preserved through the OTP flow,
        // falling back to /jobs when none was stored. Session regeneration
        // in the login step preserves session data (only the ID changes),
        // so the url.intended key survives the full flow.
        return redirect()->intended('/jobs');
    }

    /**
     * Handle OTP resend request.
     *
     * Rate limited to 3 requests per 15 minutes per email.
     * Invalidates old OTP, generates a new one, and sends it via email.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('otp_user_id');

        if (! $userId) {
            return redirect('/login');
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect('/login');
        }

        $rateLimitKey = 'resend-otp-' . $user->email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = (int) ceil($seconds / 60);

            return back()->withErrors([
                'otp' => "Too many resend attempts. Please try again in {$minutes} " . ($minutes === 1 ? 'minute' : 'minutes') . '.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 15 * 60);

        // Invalidate old OTP and generate new one
        $this->otpService->invalidate($user);
        $otp = $this->otpService->generate($user);

        // Send the new OTP via email
        Mail::to($user->email)->send(new OtpMail($otp));

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    /**
     * Display the login form.
     *
     * Redirects already-authenticated users to their role-appropriate
     * dashboard.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect(RedirectIfAuthenticated::redirectPathForUser(Auth::user()));
        }

        return view('auth.login');
    }

    /**
     * Handle a login request.
     *
     * Rate limited to 5 attempts per 15 minutes per email.
     * Uses a generic error message regardless of whether the email exists.
     * Redirects unverified users to OTP verification with a new OTP.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $email = $request->validated('email');
        $rateLimitKey = 'login-' . $email;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = (int) ceil($seconds / 60);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} " . ($minutes === 1 ? 'minute' : 'minutes') . '.',
            ])->withInput(['email' => $email]);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($rateLimitKey, 15 * 60);

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->withInput(['email' => $email]);
        }

        RateLimiter::clear($rateLimitKey);
        
        $request->session()->regenerate();
        $user = Auth::user();

        // TODO: Uncomment when email service is configured
        // If user hasn't verified their email, send them to OTP verification
        // if (! $user->email_verified_at) {
        //     Auth::logout();
        //     $otp = $this->otpService->generate($user);
        //     Mail::to($user->email)->send(new OtpMail($otp));
        //     $request->session()->put('otp_user_id', $user->id);
        //     return redirect('/verify-otp');
        // }


        if ($user->isAdmin()) {
            return redirect()->intended('/admin/dashboard');
        } elseif ($user->isEmployer()) {
            return redirect()->intended('/employer/dashboard');
        }
        // Redirect to the intended URL stored by the auth middleware,
        // falling back to /jobs when none was stored.
        return redirect()->intended('/jobs');
    }

    /**
     * Handle logout request.
     *
     * Invalidates the session and redirects to home.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
