<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'culture' => ['nullable', 'string', 'max:5000'],
            'industry' => ['nullable', 'string', 'max:100'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'employee_count' => ['nullable', 'integer', 'min:1'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:' . date('Y')],
            'is_hiring' => ['nullable', 'boolean'],
            'perks' => ['nullable', 'array'],
            'perks.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The company name field is required.',
            'name.string' => 'The company name must be a valid string.',
            'name.max' => 'The company name may not be greater than 255 characters.',
            'description.string' => 'The description must be a valid string.',
            'description.max' => 'The description may not be greater than 5000 characters.',
            'culture.string' => 'The culture must be a valid string.',
            'culture.max' => 'The culture may not be greater than 5000 characters.',
            'industry.string' => 'The industry must be a valid string.',
            'industry.max' => 'The industry may not be greater than 100 characters.',
            'website_url.url' => 'The website URL must be a valid URL.',
            'website_url.max' => 'The website URL may not be greater than 2048 characters.',
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a JPEG, PNG, or WebP image.',
            'logo.max' => 'The logo may not be greater than 2 MB.',
            'logo_url.url' => 'The logo URL must be a valid URL.',
            'logo_url.max' => 'The logo URL may not be greater than 2048 characters.',
            'employee_count.integer' => 'The employee count must be a whole number.',
            'employee_count.min' => 'The employee count must be at least 1.',
            'founded_year.integer' => 'The founded year must be a whole number.',
            'founded_year.min' => 'The founded year must be at least 1800.',
            'founded_year.max' => 'The founded year cannot be in the future.',
            'perks.array' => 'The perks must be provided as a list.',
            'perks.*.string' => 'Each perk must be a valid string.',
            'perks.*.max' => 'Each perk may not be greater than 255 characters.',
        ];
    }
}
