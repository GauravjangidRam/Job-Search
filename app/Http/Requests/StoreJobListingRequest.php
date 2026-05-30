<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobListingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            // The job_listings.salary_* columns are NOT NULL unsigned integers,
            // so these fields are required with a floor of 0.
            'salary_min' => ['required', 'integer', 'min:0'],
            'salary_max' => ['required', 'integer', 'min:0', 'gte:salary_min'],
            'job_type' => ['required', 'string', 'max:50'],
            'location_type' => ['required', 'string', 'max:50'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:255'],
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
            'title.required' => 'The job title field is required.',
            'title.max' => 'The job title may not be greater than 255 characters.',
            'description.required' => 'The job description field is required.',
            'location.required' => 'The location field is required.',
            'location.max' => 'The location may not be greater than 255 characters.',
            'salary_min.required' => 'The minimum salary field is required.',
            'salary_min.integer' => 'The minimum salary must be a whole number.',
            'salary_min.min' => 'The minimum salary must be at least 0.',
            'salary_max.required' => 'The maximum salary field is required.',
            'salary_max.integer' => 'The maximum salary must be a whole number.',
            'salary_max.min' => 'The maximum salary must be at least 0.',
            'salary_max.gte' => 'The maximum salary must be greater than or equal to the minimum salary.',
            'job_type.required' => 'The job type field is required.',
            'job_type.max' => 'The job type may not be greater than 50 characters.',
            'location_type.required' => 'The location type field is required.',
            'location_type.max' => 'The location type may not be greater than 50 characters.',
            'skills.array' => 'The skills must be provided as a list.',
            'skills.*.string' => 'Each skill must be a valid string.',
            'skills.*.max' => 'Each skill may not be greater than 255 characters.',
        ];
    }
}
