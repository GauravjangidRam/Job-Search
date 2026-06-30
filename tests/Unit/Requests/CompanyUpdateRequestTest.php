<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\CompanyUpdateRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CompanyUpdateRequestTest extends TestCase
{
    private function rules(): array
    {
        return (new CompanyUpdateRequest())->rules();
    }

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, $this->rules());
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new CompanyUpdateRequest())->authorize());
    }

    public function test_valid_data_passes(): void
    {
        $validator = $this->validate([
            'name' => 'Acme Corp',
            'description' => 'A description.',
            'industry' => 'Software',
            'website_url' => 'https://acme.example.com',
            'logo_url' => 'https://acme.example.com/logo.png',
            'employee_count' => 100,
        ]);

        $this->assertTrue($validator->passes());
    }

    public function test_name_is_required(): void
    {
        $validator = $this->validate(['name' => '']);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_optional_fields_may_be_null(): void
    {
        $validator = $this->validate([
            'name' => 'Acme Corp',
            'description' => null,
            'industry' => null,
            'website_url' => null,
            'logo_url' => null,
            'employee_count' => null,
        ]);
        $this->assertTrue($validator->passes());
    }

    public function test_website_url_must_be_valid_url(): void
    {
        $validator = $this->validate([
            'name' => 'Acme Corp',
            'website_url' => 'not-a-url',
        ]);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('website_url', $validator->errors()->toArray());
    }

    public function test_employee_count_must_be_integer_at_least_one(): void
    {
        $this->assertTrue(
            $this->validate(['name' => 'Acme Corp', 'employee_count' => 0])->fails()
        );
        $this->assertTrue(
            $this->validate(['name' => 'Acme Corp', 'employee_count' => 'abc'])->fails()
        );
        $this->assertTrue(
            $this->validate(['name' => 'Acme Corp', 'employee_count' => 1])->passes()
        );
    }

    public function test_logo_must_be_an_accepted_image(): void
    {
        $invalid = $this->validate([
            'name' => 'Acme Corp',
            'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf'),
        ]);
        $this->assertTrue($invalid->fails());
        $this->assertArrayHasKey('logo', $invalid->errors()->toArray());
        $valid = $this->validate([
            'name' => 'Acme Corp',
            'logo' => UploadedFile::fake()->image('logo.png', 64, 64),
        ]);
        $this->assertTrue($valid->passes());
    }
}
