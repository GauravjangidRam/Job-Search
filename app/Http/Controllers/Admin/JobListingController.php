<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobListingController extends Controller
{
    /**
     * The available listing statuses for filtering.
     *
     * @var list<string>
     */
    private const STATUSES = ['draft', 'active', 'closed'];

    /**
     * Display a paginated list of all job listings with optional status filter.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');

        $listings = JobListing::query()
            ->with('company')
            ->when($statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.jobs.index', [
            'listings' => $listings,
            'statusFilter' => $statusFilter,
            'statuses' => self::STATUSES,
        ]);
    }

    /**
     * Approve a job listing by setting its status to active.
     */
    public function approve(JobListing $job): RedirectResponse
    {
        $job->status = 'active';
        $job->save();

        return back()->with('success', 'Job listing approved successfully.');
    }

    /**
     * Reject a job listing by setting its status to closed.
     */
    public function reject(JobListing $job): RedirectResponse
    {
        $job->status = 'closed';
        $job->save();

        return back()->with('success', 'Job listing rejected successfully.');
    }

    /**
     * Delete a job listing and its associated applications (cascaded via FK).
     */
    public function destroy(JobListing $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('admin.jobs.index')->with('success', 'Job listing deleted successfully.');
    }
}
