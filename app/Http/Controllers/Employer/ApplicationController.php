<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    /**
     * Display a listing of applications for the authenticated employer's
     * company's job listings, with optional job listing and status filters.
     */
    public function index(Request $request): View
    {
        $companyId = Auth::user()->company_id;

        // The employer's own job listings, used both for the filter dropdown
        // and to validate the job_listing_id filter belongs to the company.
        $jobListings = JobListing::query()
            ->where('company_id', $companyId)
            ->orderBy('title')
            ->get();

        $jobListingFilter = $request->input('job_listing_id');
        $statusFilter = $request->input('status');

        // Only honour the job listing filter when it belongs to the employer.
        $allowedJobListingFilter = null;
        if ($jobListingFilter !== null && $jobListings->contains('id', (int) $jobListingFilter)) {
            $allowedJobListingFilter = (int) $jobListingFilter;
        }

        $applications = JobApplication::query()
            ->whereHas('jobListing', fn ($query) => $query->where('company_id', $companyId))
            ->when(
                $allowedJobListingFilter !== null,
                fn ($query) => $query->where('job_listing_id', $allowedJobListingFilter)
            )
            ->when(
                $statusFilter !== null && $statusFilter !== '',
                fn ($query) => $query->where('status', $statusFilter)
            )
            ->with('jobListing')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('employer.applications.index', [
            'applications' => $applications,
            'jobListings' => $jobListings,
            'jobListingFilter' => $allowedJobListingFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Display the full details of a single application.
     */
    public function show(JobApplication $application): View
    {
        $this->authorizeOwnership($application);

        $application->load('jobListing');

        return view('employer.applications.show', compact('application'));
    }

    /**
     * Update the status of an application and record the change timestamp.
     */
    public function updateStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorizeOwnership($application);

        $validated = $request->validate([
            'status' => 'required|in:applied,reviewed,shortlisted,rejected',
        ]);

        $statusChanged = $application->status !== $validated['status'];
        $application->status = $validated['status'];
        $application->status_updated_at = now();
        $application->save();

        if ($statusChanged && $application->user) {
            $application->user->notify(new \App\Notifications\ApplicationStatusUpdatedNotification($application));
        }

        return redirect()
            ->back()
            ->with('success', 'Application status updated.');
    }

    /**
     * Download the resume attached to an application.
     */
    public function downloadResume(JobApplication $application): StreamedResponse
    {
        $this->authorizeOwnership($application);

        abort_if(empty($application->resume_path), 404);

        return Storage::disk('local')->download($application->resume_path);
    }

    /**
     * Ensure the application's job listing belongs to the authenticated
     * employer's company.
     */
    private function authorizeOwnership(JobApplication $application): void
    {
        abort_if(
            $application->jobListing->company_id !== Auth::user()->company_id,
            403
        );
    }
}
