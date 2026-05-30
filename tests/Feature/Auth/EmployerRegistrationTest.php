<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\AuthController;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Feature: full-platform-features, Property 14: Employer registration creates linked records
 *
 * For any valid employer registration data, the system creates exactly one
 * User record with role "employer" and exactly one Company record, and the
 * user's company_id references the created company's id.
 *
 * AuthController::employerRegister() wraps the Company + User creation in a
 * DB transaction, links the user to the company via company_id, sends an OTP,
 * and redirects to /verify-otp.
 *
 * **Validates: Requirements 10.2**
 */
class EmployerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The employer registration route is registered globally in a later
        // task (17.1). Register it here so the controller and form request can
        // be exercised end-to-end in isolation.
        Route::middleware('web')
            ->post('/employer/register', [AuthController::class, 'employerRegister'])
            ->name('employer.register');

        // Routes registered after the app boots are not in the name lookup yet.
        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    /**
     * Property 14: Employer registration creates linked records.
     *
     * For randomized valid registration payloads, posting to /employer/register
     * creates exactly one new User (role "employer") and exactly one new
     * Company, with the user's company_id pointing at the new company, and
     * redirects into the OTP verification flow.
     *
     * **Validates: Requirements 10.2**
     */
    #[DataProvider('employerRegistrationProvider')]
    public function test_employer_registration_creates_linked_records(array $payload): void
    {
        // Avoid dispatching the real OTP email during registration.
        Mail::fake();

        $usersBefore = User::count();
        $companiesBefore = Company::count();

        $response = $this->post('/employer/register', $payload);

        // The flow finishes by sending the user to OTP verification.
        $response->assertRedirect('/verify-otp');

        // Exactly one User and one Company were created.
        $this->assertSame($usersBefore + 1, User::count(), 'Expected exactly one new User record.');
        $this->assertSame($companiesBefore + 1, Company::count(), 'Expected exactly one new Company record.');

        $user = User::where('email', $payload['email'])->sole();
        $company = Company::latest('id')->first();

        // The created user is an employer linked to the created company.
        $this->assertSame('employer', $user->role);
        $this->assertNotNull($user->company_id);
        $this->assertSame($company->id, $user->company_id, "User's company_id must reference the created company.");

        // The company captured the submitted company fields.
        $this->assertSame($payload['company_name'], $company->name);
        $this->assertSame($payload['industry'], $company->industry);
        $this->assertSame($payload['description'], $company->description);
    }

    /**
     * Generates 100+ randomized valid employer registration payloads with
     * unique emails, random names, company names, industries, and
     * descriptions, all within the EmployerRegisterRequest validation bounds.
     *
     * @return array<string, array{array<string, string>}>
     */
    public static function employerRegistrationProvider(): array
    {
        // Seed for reproducibility so any counterexample is repeatable.
        $faker = \Faker\Factory::create();
        $faker->seed(20250105);

        $cases = [];

        for ($i = 0; $i < 100; $i++) {
            $payload = [
                'name' => $faker->name(),
                // Guarantee uniqueness across the provider with an index prefix.
                'email' => "employer{$i}_" . $faker->unique()->safeEmail(),
                'password' => $faker->password(8, 60),
                'company_name' => $faker->company(),
                // industry max:100
                'industry' => substr($faker->jobTitle(), 0, 100),
                // description max:5000
                'description' => substr($faker->paragraph(3), 0, 5000),
            ];

            $cases["case {$i}: {$payload['email']}"] = [$payload];
        }

        return $cases;
    }
}
