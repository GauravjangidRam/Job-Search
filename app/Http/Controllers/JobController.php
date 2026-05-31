<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Services\ApplicationNotificationService;
use App\Services\FileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Display a paginated listing of jobs with optional filters and search.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $query = JobListing::active();

        // Filter by job_type when present
        if ($request->filled('job_type')) {
            $query->where('job_type', $request->job_type);
        }

        // Filter by location_type when present
        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        // Filter by salary_min: show jobs whose salary_max >= requested minimum
        if ($request->filled('salary_min')) {
            $query->where('salary_max', '>=', (int) $request->salary_min);
        }

        // Filter by salary_max: show jobs whose salary_min <= requested maximum
        if ($request->filled('salary_max')) {
            $query->where('salary_min', '<=', (int) $request->salary_max);
        }

        // Filter by company_name: partial case-insensitive match
        if ($request->filled('company_name')) {
            $query->where('company_name', 'LIKE', '%' . $request->company_name . '%');
        }

        // Search: match title, company_name, or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by location (city name): partial match
        if ($request->filled('location')) {
            $location = $request->location;
            $query->where(function ($q) use ($location) {
                $q->where('location', 'LIKE', "%{$location}%")
                  ->orWhere('location_type', 'LIKE', "%{$location}%");
            });
        }

        // Order by newest first
        $query->orderBy('created_at', 'desc');

        // Paginate at 12 per page, appending query string
        $jobs = $query->paginate(12)->appends($request->query());

        // Handle page overflow: redirect to last available page
        if ($jobs->currentPage() > $jobs->lastPage() && $jobs->lastPage() > 0) {
            return redirect()->to(
                $jobs->url($jobs->lastPage())
            );
        }

        return view('jobs.index', [
            'jobs' => $jobs,
            'filters' => [
                'job_type' => $request->job_type,
                'location_type' => $request->location_type,
                'salary_min' => $request->salary_min,
                'salary_max' => $request->salary_max,
                'search' => $request->search,
                'company_name' => $request->company_name,
            ],
        ]);
    }

    /**
     * Display a single job listing.
     */
    public function show(JobListing $job): View
    {
        return view('jobs.show', [
            'job' => $job,
        ]);
    }

    /**
     * Display the job application page.
     *
     * Passes the authenticated user so the form can pre-fill the name and
     * email fields from the user's profile data (Requirement 4.6).
     */
    public function apply(JobListing $job): View
    {
        $user = Auth::user();

        $hasApplied = JobApplication::where('user_id', $user?->id)
            ->where('job_listing_id', $job->id)
            ->exists();

        return view('jobs.apply', [
            'job' => $job,
            'hasApplied' => $hasApplied,
            'user' => $user,
        ]);
    }

    /**
     * Submit a job application.
     *
     * Validates the submission via JobApplicationRequest, prevents duplicate
     * applications, stores the uploaded resume, persists the application, and
     * notifies the employer.
     */
    public function submitApplication(
        JobApplicationRequest $request,
        JobListing $job,
        FileUploadService $fileUploadService
    ): RedirectResponse {
        $userId = Auth::id();

        // Prevent duplicate submissions for the same seeker + listing (Requirement 4.5)
        $existingApplication = JobApplication::where('user_id', $userId)
            ->where('job_listing_id', $job->id)
            ->first();

        if ($existingApplication !== null) {
            return redirect()->route('jobs.apply', $job)
                ->with('error', 'You have already applied to this job. Your existing application is being reviewed.');
        }

        // Store the uploaded resume in a dedicated directory with a unique name (Requirement 4.4)
        $resumePath = $fileUploadService->uploadResume($request->file('resume'), $userId);

        // Persist the application details, including the stored resume path (Requirement 4.2)
        $application = JobApplication::create([
            'user_id' => $userId,
            'job_listing_id' => $job->id,
            'applicant_name' => $request->validated('applicant_name'),
            'applicant_email' => $request->validated('applicant_email'),
            'applicant_phone' => $request->validated('applicant_phone'),
            'resume_path' => $resumePath,
            'cover_letter' => $request->validated('cover_letter'),
            'additional_info' => $request->validated('additional_info'),
            'status_updated_at' => now(),
        ]);

        // Notify the employer; the service guards against failures internally (Requirement 16.1)
        app(ApplicationNotificationService::class)->notifyEmployer($application);

        return redirect()->route('jobs.apply', $job)
            ->with('success', 'Your application has been submitted successfully!');
    }
}
