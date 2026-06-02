<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function edit(): View
    {
        $company = $this->resolveCompany();
        return view('employer.company.edit', ['company' => $company]);
    }

    public function update(CompanyUpdateRequest $request, CloudinaryService $cloudinary): RedirectResponse
    {
        $company = $this->resolveCompany();

        $data = $request->safe()->only([
            'name', 'description', 'culture', 'industry',
            'website_url', 'logo_url', 'employee_count',
            'founded_year', 'is_hiring',
        ]);

        if ($request->has('perks')) {
            $data['perks'] = array_values(
                array_filter($request->validated('perks') ?? [], 
                fn($p) => !empty(trim($p)))
            );
        }

        // Cloudinary upload (replaces local storage)
        if ($request->hasFile('logo')) {
            $data['logo_url'] = $cloudinary->upload(
                $request->file('logo'), 
                'jobhub/company-logos'
            );
        }

        $company->update($data);

        return redirect()
            ->route('employer.company.edit')
            ->with('success', 'Company profile updated successfully.');
    }

    private function resolveCompany(): Company
    {
        $company = Auth::user()?->company;
        abort_if($company === null, 404);
        return $company;
    }
}