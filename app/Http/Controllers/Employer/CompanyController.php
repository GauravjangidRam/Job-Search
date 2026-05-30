<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyUpdateRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * The filesystem disk used for storing publicly accessible company logos.
     */
    private const LOGO_DISK = 'public';

    /**
     * Directory (relative to the disk root) where company logos are stored.
     */
    private const LOGO_DIRECTORY = 'company-logos';

    /**
     * Display the company profile edit form for the authenticated employer.
     */
    public function edit(): View
    {
        $company = $this->resolveCompany();

        return view('employer.company.edit', [
            'company' => $company,
        ]);
    }

    /**
     * Validate and persist changes to the authenticated employer's company.
     */
    public function update(CompanyUpdateRequest $request): RedirectResponse
    {
        $company = $this->resolveCompany();

        $data = $request->safe()->only([
            'name',
            'description',
            'culture',
            'industry',
            'website_url',
            'logo_url',
            'employee_count',
            'founded_year',
            'is_hiring',
        ]);

        // Filter out empty perks and store as array
        if ($request->has('perks')) {
            $data['perks'] = array_values(array_filter($request->validated('perks') ?? [], fn($p) => !empty(trim($p))));
        }

        // A freshly uploaded logo file takes precedence over a provided URL.
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store(self::LOGO_DIRECTORY, self::LOGO_DISK);

            $data['logo_url'] = Storage::disk(self::LOGO_DISK)->url($path);
        }

        $company->update($data);

        return redirect()
            ->route('employer.company.edit')
            ->with('success', 'Company profile updated successfully.');
    }

    /**
     * Resolve the authenticated employer's company or fail with a 404.
     */
    private function resolveCompany(): Company
    {
        $company = Auth::user()?->company;

        abort_if($company === null, 404);

        return $company;
    }
}
