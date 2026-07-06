<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CompanyApprovedMail;
use App\Mail\CompanyRejectedMail;
use App\Models\Company;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyController extends Controller
{
    /**
     * Display a paginated list of all companies with job count and linked employer.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');

        $companies = Company::query()
            ->withCount('jobListings')
            ->with('employers')
            ->when($statusFilter, function ($query, $status) {
                $query->where('verification_status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = Company::where('verification_status', 'pending')->count();

        return view('admin.companies.index', [
            'companies' => $companies,
            'statusFilter' => $statusFilter,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Approve a company — allows them to post jobs.
     */
    public function approve(Company $company): RedirectResponse
    {
        $company->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        $company->employers->each(
            fn ($employer) => Mail::to($employer->email)->send(new CompanyApprovedMail($company))
        );

        return back()->with('success', "Company \"{$company->name}\" has been approved.");
    }

    /**
     * Reject a company with a reason.
     */
    public function reject(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $reason = $request->input('rejection_reason', 'Company did not meet verification requirements.');

        $company->update([
            'verification_status' => 'rejected',
            'verified_at' => null,
            'rejection_reason' => $reason,
        ]);

        $company->employers->each(
            fn ($employer) => Mail::to($employer->email)->send(new CompanyRejectedMail($company, $reason))
        );

        return back()->with('success', "Company \"{$company->name}\" has been rejected.");
    }

    /**
     * Delete the specified company.
     */
    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}
