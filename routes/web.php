<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CareerInsightController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Employer;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Job routes
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{hash}', [JobController::class, 'show'])->where('hash', '[a-zA-Z0-9]+')->name('jobs.show');

// Guest routes (register & login)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,15');
    Route::get('/employer/register', [AuthController::class, 'showEmployerRegister'])->name('employer.register');
    Route::post('/employer/register', [AuthController::class, 'employerRegister']);

    // Google OAuth
    Route::get('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback']);
});

// OTP verification routes
Route::get('/verify-otp', [AuthController::class, 'showOtpVerification'])->name('verify-otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,15');

// Companies
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{slug}', [CompanyController::class, 'show'])->name('companies.show');

// Career Insights
Route::get('/insights', [CareerInsightController::class, 'index'])->name('insights.index');

// Resume
Route::get('/resume', [ResumeController::class, 'index'])->name('resume.index');

// Authenticated routes (any authenticated user)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/jobs/{hash}/apply', [JobController::class, 'apply'])->where('hash', '[a-zA-Z0-9]+')->name('jobs.apply');
    Route::post('/jobs/{hash}/apply', [JobController::class, 'submitApplication'])->where('hash', '[a-zA-Z0-9]+')->name('jobs.submitApplication');
    Route::post('/bookmarks/{hash}/toggle', [BookmarkController::class, 'toggle'])->where('hash', '[a-zA-Z0-9]+')->name('bookmarks.toggle');
});

// Seeker routes (auth + role:seeker)
Route::middleware(['auth', 'role:seeker'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
});

// Employer routes (auth + role:employer)
Route::middleware(['auth', 'role:employer'])->prefix('employer')->name('employer.')->group(function () {
    Route::get('/dashboard', [Employer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/company', [Employer\CompanyController::class, 'edit'])->name('company.edit');
    Route::put('/company', [Employer\CompanyController::class, 'update'])->name('company.update');
    Route::resource('jobs', Employer\JobListingController::class)->except(['show']);
    Route::get('/applications', [Employer\ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [Employer\ApplicationController::class, 'show'])->name('applications.show');
    Route::patch('/applications/{application}/status', [Employer\ApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    Route::get('/applications/{application}/resume', [Employer\ApplicationController::class, 'downloadResume'])->name('applications.resume');
});

// Admin routes (auth + role:admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [Admin\UserController::class, 'updateRole'])->name('users.updateRole');
    Route::get('/jobs', [Admin\JobListingController::class, 'index'])->name('jobs.index');
    Route::post('/jobs/{job}/approve', [Admin\JobListingController::class, 'approve'])->name('jobs.approve');
    Route::post('/jobs/{job}/reject', [Admin\JobListingController::class, 'reject'])->name('jobs.reject');
    Route::delete('/jobs/{job}', [Admin\JobListingController::class, 'destroy'])->name('jobs.destroy');
    Route::get('/companies', [Admin\CompanyController::class, 'index'])->name('companies.index');
    Route::post('/companies/{company}/approve', [Admin\CompanyController::class, 'approve'])->name('companies.approve');
    Route::post('/companies/{company}/reject', [Admin\CompanyController::class, 'reject'])->name('companies.reject');
    Route::delete('/companies/{company}', [Admin\CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}/status', [Admin\ApplicationController::class, 'updateStatus'])->name('applications.updateStatus');
    Route::resource('testimonials', Admin\TestimonialController::class)->except(['show']);
    Route::resource('insights', Admin\CareerInsightController::class)->except(['show']);
});
