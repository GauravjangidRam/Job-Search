<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The application statuses tracked on the employer dashboard. Every status
     * is represented in the breakdown so missing statuses default to a count
     * of zero.
     *
     * @var list<string>
     */
    private const APPLICATION_STATUSES = ['applied', 'reviewed', 'shortlisted', 'rejected'];

    /**
     * Display the employer statistics overview for the authenticated employer's
     * company.
     */
    public function index(): View
    {
        $companyId = Auth::user()->company_id;

        // Total active job listings for the employer's company (Requirement 17.1).
        $totalActiveListings = JobListing::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        // Constrain applications to the company's job listings. Reused across the
        // remaining statistics so scoping stays consistent.
        $companyApplications = fn () => JobApplication::query()
            ->whereHas('jobListing', fn ($query) => $query->where('company_id', $companyId));

        // Total applications received across all of the company's listings
        // (Requirement 17.2).
        $totalApplications = $companyApplications()->count();

        // Breakdown of applications by status (Requirement 17.3). Start from a
        // baseline of zero for every known status so absent statuses still appear.
        $statusCounts = $companyApplications()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $applicationsByStatus = [];
        foreach (self::APPLICATION_STATUSES as $status) {
            $applicationsByStatus[$status] = (int) ($statusCounts[$status] ?? 0);
        }

        // The 5 most recent applications with applicant name, job title, and date
        // (Requirement 17.4).
        $recentApplications = $companyApplications()
            ->with('jobListing')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (JobApplication $application) => [
                'applicant_name' => $application->applicant_name,
                'job_title' => $application->jobListing?->title,
                'date' => $application->created_at,
            ]);

        return view('employer.dashboard', compact(
            'totalActiveListings',
            'totalApplications',
            'applicationsByStatus',
            'recentApplications',
        ));
    }
}
