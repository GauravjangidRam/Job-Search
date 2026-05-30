<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * Display a paginated list of all job applications.
     * Eager-loads jobListing.company for display.
     *
     * Requirement 8.2
     */
    public function index(Request $request): View
    {
        $applications = JobApplication::query()
            ->with('jobListing.company')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
        ]);
    }

    /**
     * Update the status of a job application.
     * Validates status against allowed values and records the timestamp.
     *
     * Requirement 8.4
     */
    public function updateStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:applied,reviewed,shortlisted,rejected',
        ]);

        $application->status = $validated['status'];
        $application->status_updated_at = now();
        $application->save();

        return redirect()
            ->back()
            ->with('success', 'Application status updated.');
    }
}
