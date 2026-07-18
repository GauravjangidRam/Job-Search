<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Services\ApplicationNotificationService;
use App\Services\FileUploadService;
use App\Services\ResumeAnalysisService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $query = JobListing::active();

        foreach (['job_type', 'location_type'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        if ($request->filled('salary_min')) {
            $query->where('salary_max', '>=', (int) $request->input('salary_min'));
        }

        if ($request->filled('salary_max')) {
            $query->where('salary_min', '<=', (int) $request->input('salary_max'));
        }

        $this->applyTextFilter($query, $request->input('company_name'), ['company_name']);
        $this->applyTextFilter($query, $request->input('search'), ['title', 'company_name', 'description']);
        $this->applyTextFilter($query, $request->input('location'), ['location', 'location_type']);

        $jobs = $query->latest()->paginate(12)->withQueryString();

        if ($jobs->currentPage() > $jobs->lastPage() && $jobs->lastPage() > 0) {
            return redirect()->to($jobs->url($jobs->lastPage()));
        }

        return view('jobs.index', [
            'jobs' => $jobs,
            'filters' => $request->only(['job_type', 'location_type', 'salary_min', 'salary_max', 'search', 'company_name']),
        ]);
    }

    public function show(string $hash): View
    {
        return view('jobs.show', ['job' => $this->activeJobFromHash($hash)]);
    }

    public function apply(string $hash): View
    {
        $job = $this->activeJobFromHash($hash);
        $user = Auth::user();

        return view('jobs.apply', [
            'job' => $job,
            'hasApplied' => JobApplication::where('user_id', $user->id)->where('job_listing_id', $job->id)->exists(),
            'user' => $user,
        ]);
    }

    public function submitApplication(
        JobApplicationRequest $request,
        string $hash,
        FileUploadService $fileUploadService,
        ResumeAnalysisService $resumeAnalysisService,
    ): RedirectResponse {
        $job = $this->activeJobFromHash($hash);
        $userId = Auth::id();

        if (JobApplication::where('user_id', $userId)->where('job_listing_id', $job->id)->exists()) {
            return redirect()->route('jobs.apply', $job->hashed_id)->with('error', 'You have already applied to this job.');
        }

        $resumePath = $fileUploadService->uploadResume($request->file('resume'), $userId);

        try {
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
        } catch (UniqueConstraintViolationException) {
            Storage::disk('local')->delete($resumePath);

            return redirect()->route('jobs.apply', $job->hashed_id)->with('error', 'You have already applied to this job.');
        }

        app(ApplicationNotificationService::class)->notifyEmployer($application);

        try {
            $resumeAnalysisService->analyze($application, $resumePath);
        } catch (\Throwable $e) {
            Log::error('Resume analysis failed.', ['application_id' => $application->id, 'exception' => $e]);
        }

        return redirect()->route('jobs.apply', $job->hashed_id)->with('success', 'Your application has been submitted successfully!');
    }

    private function activeJobFromHash(string $hash): JobListing
    {
        $job = JobListing::findByHash($hash);
        abort_if(! $job || $job->status !== 'active', 404);

        return $job;
    }

    /** @param array<int, string> $columns */
    private function applyTextFilter($query, mixed $value, array $columns): void
    {
        if (! is_string($value) || ($value = trim($value)) === '') {
            return;
        }

        $value = mb_substr($value, 0, 100);
        $escaped = addcslashes($value, '%_\\');

        $query->where(function ($query) use ($columns, $escaped) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $query->{$method}($column, 'LIKE', "%{$escaped}%");
            }
        });
    }
}
