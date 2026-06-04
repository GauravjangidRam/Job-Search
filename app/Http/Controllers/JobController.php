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
    public function index(Request $request): View|RedirectResponse
    {
        $query = JobListing::active();

        if ($request->filled('job_type'))
            $query->where('job_type', $request->job_type);

        if ($request->filled('location_type'))
            $query->where('location_type', $request->location_type);

        if ($request->filled('salary_min'))
            $query->where('salary_max', '>=', (int) $request->salary_min);

        if ($request->filled('salary_max'))
            $query->where('salary_min', '<=', (int) $request->salary_max);

        if ($request->filled('company_name'))
            $query->where('company_name', 'LIKE', '%' . $request->company_name . '%');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('company_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $location = $request->location;
            $query->where(function ($q) use ($location) {
                $q->where('location', 'LIKE', "%{$location}%")
                  ->orWhere('location_type', 'LIKE', "%{$location}%");
            });
        }

        $query->orderBy('created_at', 'desc');
        $jobs = $query->paginate(12)->appends($request->query());

        if ($jobs->currentPage() > $jobs->lastPage() && $jobs->lastPage() > 0) {
            return redirect()->to($jobs->url($jobs->lastPage()));
        }

        return view('jobs.index', [
            'jobs' => $jobs,
            'filters' => [
                'job_type'      => $request->job_type,
                'location_type' => $request->location_type,
                'salary_min'    => $request->salary_min,
                'salary_max'    => $request->salary_max,
                'search'        => $request->search,
                'company_name'  => $request->company_name,
            ],
        ]);
    }

    // ✅ $hash string lega, model se decode karega
    public function show(string $hash): View
    {
        $job = JobListing::findByHash($hash);
        abort_if(!$job, 404);
        return view('jobs.show', ['job' => $job]);
    }

    public function apply(JobListing $job): View
    {
        $user = Auth::user();

        $hasApplied = JobApplication::where('user_id', $user?->id)
            ->where('job_listing_id', $job->id) // ✅ $job->id use karo
            ->exists();

        return view('jobs.apply', [
            'job'        => $job,
            'hasApplied' => $hasApplied,
            'user'       => $user,
        ]);
    }

    public function submitApplication(
        JobApplicationRequest $request,
        JobListing $job,
        FileUploadService $fileUploadService
    ): RedirectResponse {
        $userId = Auth::id();

        $existingApplication = JobApplication::where('user_id', $userId)
            ->where('job_listing_id', $job->id) // ✅ $job->id use karo
            ->first();

        if ($existingApplication !== null) {
            return redirect()->route('jobs.apply', $job->hashed_id)
                ->with('error', 'You have already applied to this job.');
        }

        $resumePath = $fileUploadService->uploadResume($request->file('resume'), $userId);

        $application = JobApplication::create([
            'user_id'          => $userId,
            'job_listing_id'   => $job->id, // ✅ $job->id use karo
            'applicant_name'   => $request->validated('applicant_name'),
            'applicant_email'  => $request->validated('applicant_email'),
            'applicant_phone'  => $request->validated('applicant_phone'),
            'resume_path'      => $resumePath,
            'cover_letter'     => $request->validated('cover_letter'),
            'additional_info'  => $request->validated('additional_info'),
            'status_updated_at' => now(),
        ]);

        app(ApplicationNotificationService::class)->notifyEmployer($application);

        return redirect()->route('jobs.apply', $job->hashed_id)
            ->with('success', 'Your application has been submitted successfully!');
    }
}