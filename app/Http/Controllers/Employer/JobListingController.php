<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobListingRequest;
use App\Http\Requests\UpdateJobListingRequest;
use App\Models\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobListingController extends Controller
{
    /**
     * Display a listing of the authenticated employer's job listings.
     */
    public function index(): View
    {
        $jobs = JobListing::query()
            ->where('company_id', Auth::user()->company_id)
            ->latest()
            ->paginate(15);

        return view('employer.jobs.index', compact('jobs'));
    }

    /**
     * Show the form for creating a new job listing.
     */
    public function create(): View
    {
        $company = Auth::user()->company;

        // Block job creation if company is not approved
        if ($company && $company->verification_status !== 'approved') {
            return view('employer.jobs.pending-approval', ['company' => $company]);
        }

        return view('employer.jobs.create');
    }

    /**
     * Store a newly created job listing scoped to the employer's company.
     */
    public function store(StoreJobListingRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->safe()->only([
            'title',
            'description',
            'location',
            'salary_min',
            'salary_max',
            'job_type',
            'location_type',
            'skills',
        ]);

        // The skills column is a NOT NULL json column, but the form request
        // treats skills as optional (nullable). Default to an empty array so an
        // omitted value still satisfies the database constraint.
        $data['skills'] = $request->validated('skills') ?? [];

        $data['company_id'] = $user->company_id;
        $data['company_name'] = $user->company?->name;
        $data['status'] = 'active';

        JobListing::create($data);

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', 'Job listing created successfully.');
    }

    /**
     * Show the form for editing the specified job listing.
     */
    public function edit(JobListing $job): View
    {
        $this->authorizeOwnership($job);

        return view('employer.jobs.edit', compact('job'));
    }

    /**
     * Update the specified job listing.
     */
    public function update(UpdateJobListingRequest $request, JobListing $job): RedirectResponse
    {
        $this->authorizeOwnership($job);

        $data = $request->safe()->only([
            'title',
            'description',
            'location',
            'salary_min',
            'salary_max',
            'job_type',
            'location_type',
            'skills',
            'status',
        ]);

        // The skills column is a NOT NULL json column, but the form request
        // treats skills as optional (nullable). Default to an empty array so an
        // omitted value still satisfies the database constraint.
        $data['skills'] = $request->validated('skills') ?? [];

        $job->update($data);

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', 'Job listing updated successfully.');
    }

    /**
     * Remove the specified job listing. Associated applications cascade via FK.
     */
    public function destroy(JobListing $job): RedirectResponse
    {
        $this->authorizeOwnership($job);

        $job->delete();

        return redirect()
            ->route('employer.jobs.index')
            ->with('success', 'Job listing deleted successfully.');
    }

    /**
     * Ensure the given job listing belongs to the authenticated employer's company.
     */
    private function authorizeOwnership(JobListing $job): void
    {
        abort_if($job->company_id !== Auth::user()->company_id, 403);
    }
}
