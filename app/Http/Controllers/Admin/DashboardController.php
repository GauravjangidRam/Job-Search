<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * The user roles tracked on the admin dashboard.
     *
     * @var list<string>
     */
    private const USER_ROLES = ['seeker', 'employer', 'admin'];

    /**
     * The job listing statuses tracked on the admin dashboard.
     *
     * @var list<string>
     */
    private const LISTING_STATUSES = ['draft', 'active', 'closed'];

    /**
     * The application statuses tracked on the admin dashboard.
     *
     * @var list<string>
     */
    private const APPLICATION_STATUSES = ['applied', 'reviewed', 'shortlisted', 'rejected'];

    /**
     * Display the admin dashboard with platform-wide statistics.
     */
    public function index(): View
    {
        $totalUsers = User::count();
        $totalListings = JobListing::count();
        $totalApplications = JobApplication::count();
        $totalCompanies = Company::count();

        // Users grouped by role (Requirement 9.2). Default missing roles to 0.
        $roleCounts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $usersByRole = [];
        foreach (self::USER_ROLES as $role) {
            $usersByRole[$role] = (int) ($roleCounts[$role] ?? 0);
        }

        // Listings grouped by status (Requirement 9.2). Default missing statuses to 0.
        $listingStatusCounts = JobListing::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $listingsByStatus = [];
        foreach (self::LISTING_STATUSES as $status) {
            $listingsByStatus[$status] = (int) ($listingStatusCounts[$status] ?? 0);
        }

        // Applications grouped by status (Requirement 9.2). Default missing statuses to 0.
        $applicationStatusCounts = JobApplication::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $applicationsByStatus = [];
        foreach (self::APPLICATION_STATUSES as $status) {
            $applicationsByStatus[$status] = (int) ($applicationStatusCounts[$status] ?? 0);
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalListings',
            'totalApplications',
            'totalCompanies',
            'usersByRole',
            'listingsByStatus',
            'applicationsByStatus',
        ));
    }
}
