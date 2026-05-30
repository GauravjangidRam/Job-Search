<?php

namespace Tests\Feature\Employer;

use App\Http\Controllers\Employer\CompanyController;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Routes for the employer company profile are registered globally in a
        // later task (17.1). Register them here so the controller, form request,
        // and view can be exercised end-to-end in isolation.
        Route::middleware('web')->group(function () {
            Route::get('/employer/company', [CompanyController::class, 'edit'])
                ->name('employer.company.edit');
            Route::put('/employer/company', [CompanyController::class, 'update'])
                ->name('employer.company.update');
        });

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    private function employerWithCompany(array $companyAttributes = []): array
    {
        $company = Company::create(array_merge([
            'name' => 'Acme Corp',
            'industry' => 'Software',
            'description' => 'We build things.',
        ], $companyAttributes));

        $user = User::factory()->create([
            'role' => 'employer',
            'company_id' => $company->id,
        ]);

        return [$user, $company];
    }

    public function test_edit_displays_company_profile_form_with_company(): void
    {
        [$user, $company] = $this->employerWithCompany();

        $response = $this->actingAs($user)->get('/employer/company');

        $response->assertStatus(200);
        $response->assertViewIs('employer.company.edit');
        $response->assertViewHas('company');
        $this->assertEquals($company->id, $response->viewData('company')->id);
        $response->assertSee('Acme Corp');
    }

    public function test_edit_aborts_404_when_employer_has_no_company(): void
    {
        $user = User::factory()->create([
            'role' => 'employer',
            'company_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/employer/company');

        $response->assertStatus(404);
    }

    public function test_update_persists_changes_and_redirects_with_success(): void
    {
        [$user, $company] = $this->employerWithCompany();

        $response = $this->actingAs($user)->put('/employer/company', [
            'name' => 'Acme Industries',
            'description' => 'A new description.',
            'industry' => 'Manufacturing',
            'website_url' => 'https://acme.example.com',
            'employee_count' => 250,
        ]);

        $response->assertRedirect(route('employer.company.edit'));
        $response->assertSessionHas('success');

        $company->refresh();
        $this->assertEquals('Acme Industries', $company->name);
        $this->assertEquals('A new description.', $company->description);
        $this->assertEquals('Manufacturing', $company->industry);
        $this->assertEquals('https://acme.example.com', $company->website_url);
        $this->assertEquals(250, $company->employee_count);
    }

    public function test_update_requires_name(): void
    {
        [$user] = $this->employerWithCompany();

        $response = $this->actingAs($user)->put('/employer/company', [
            'name' => '',
            'description' => 'Still here.',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_rejects_invalid_website_url(): void
    {
        [$user] = $this->employerWithCompany();

        $response = $this->actingAs($user)->put('/employer/company', [
            'name' => 'Acme Corp',
            'website_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('website_url');
    }

    public function test_update_rejects_employee_count_below_one(): void
    {
        [$user] = $this->employerWithCompany();

        $response = $this->actingAs($user)->put('/employer/company', [
            'name' => 'Acme Corp',
            'employee_count' => 0,
        ]);

        $response->assertSessionHasErrors('employee_count');
    }

    public function test_update_stores_uploaded_logo_on_public_disk(): void
    {
        Storage::fake('public');

        [$user, $company] = $this->employerWithCompany();

        $response = $this->actingAs($user)->put('/employer/company', [
            'name' => 'Acme Corp',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
        ]);

        $response->assertRedirect(route('employer.company.edit'));

        $storedFiles = Storage::disk('public')->allFiles('company-logos');
        $this->assertCount(1, $storedFiles);

        $company->refresh();
        $this->assertStringContainsString('/storage/company-logos/', $company->logo_url);
    }

    public function test_update_rejects_non_image_logo(): void
    {
        Storage::fake('public');

        [$user] = $this->employerWithCompany();

        $response = $this->actingAs($user)->put('/employer/company', [
            'name' => 'Acme Corp',
            'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('logo');
    }
}
