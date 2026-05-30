<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ], $overrides);
    }

    private function validate(array $data): \Illuminate\Validation\Validator
    {
        $request = new RegisterRequest();

        return Validator::make($data, $request->rules(), $request->messages());
    }

    public function test_valid_data_passes_validation(): void
    {
        $validator = $this->validate($this->validData());

        $this->assertTrue($validator->passes());
    }

    public function test_name_is_required(): void
    {
        $validator = $this->validate($this->validData(['name' => '']));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The name field is required.', $validator->errors()->first('name'));
    }

    public function test_name_must_be_string(): void
    {
        $validator = $this->validate($this->validData(['name' => ['array']]));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The name must be a valid string.', $validator->errors()->first('name'));
    }

    public function test_name_max_255_characters(): void
    {
        $validator = $this->validate($this->validData(['name' => str_repeat('a', 256)]));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The name may not be greater than 255 characters.', $validator->errors()->first('name'));
    }

    public function test_name_at_255_characters_passes(): void
    {
        $validator = $this->validate($this->validData(['name' => str_repeat('a', 255)]));

        $this->assertFalse($validator->errors()->has('name'));
    }

    public function test_email_is_required(): void
    {
        $validator = $this->validate($this->validData(['email' => '']));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The email field is required.', $validator->errors()->first('email'));
    }

    public function test_email_must_be_valid_format(): void
    {
        $validator = $this->validate($this->validData(['email' => 'not-an-email']));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The email format is invalid.', $validator->errors()->first('email'));
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $validator = $this->validate($this->validData(['email' => 'existing@example.com']));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The email is already registered.', $validator->errors()->first('email'));
    }

    public function test_email_max_255_characters(): void
    {
        $email = str_repeat('a', 247) . '@test.com';

        $validator = $this->validate($this->validData(['email' => $email]));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The email may not be greater than 255 characters.', $validator->errors()->first('email'));
    }

    public function test_password_is_required(): void
    {
        $validator = $this->validate($this->validData(['password' => '']));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The password field is required.', $validator->errors()->first('password'));
    }

    public function test_password_min_8_characters(): void
    {
        $validator = $this->validate($this->validData(['password' => 'short']));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The password must be at least 8 characters.', $validator->errors()->first('password'));
    }

    public function test_password_at_8_characters_passes(): void
    {
        $validator = $this->validate($this->validData(['password' => 'exactly8']));

        $this->assertFalse($validator->errors()->has('password'));
    }

    public function test_password_max_72_characters(): void
    {
        $validator = $this->validate($this->validData(['password' => str_repeat('a', 73)]));

        $this->assertTrue($validator->fails());
        $this->assertEquals('The password may not be greater than 72 characters.', $validator->errors()->first('password'));
    }

    public function test_password_at_72_characters_passes(): void
    {
        $validator = $this->validate($this->validData(['password' => str_repeat('a', 72)]));

        $this->assertFalse($validator->errors()->has('password'));
    }

    public function test_authorize_returns_true(): void
    {
        $request = new RegisterRequest();

        $this->assertTrue($request->authorize());
    }
}
