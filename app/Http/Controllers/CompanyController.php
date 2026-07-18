<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a paginated listing of companies sorted alphabetically by name.
     */
    public function index(): View
    {
        $companies = Company::query()
            ->where('verification_status', 'approved')
            ->orderBy('name', 'asc')
            ->paginate(12);

        return view('companies.index', [
            'companies' => $companies,
        ]);
    }

    /**
     * Display the specified company with its associated job listings.
     */
    public function show(string $slug): View
    {
        $company = Company::where('slug', $slug)
            ->where('verification_status', 'approved')
            ->firstOrFail();

        $jobListings = $company->jobListings()
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('companies.show', [
            'company' => $company,
            'jobListings' => $jobListings,
        ]);
    }
}
