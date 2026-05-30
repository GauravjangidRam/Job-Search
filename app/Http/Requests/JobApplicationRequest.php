<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobApplicationRequest extends FormRequest
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
            'applicant_name' => ['required', 'string', 'max:255'],
            'applicant_email' => ['required', 'email', 'max:255'],
            'applicant_phone' => ['nullable', 'string', 'max:20'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'additional_info' => ['nullable', 'string', 'max:5000'],
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
            'applicant_name.required' => 'The name field is required.',
            'applicant_name.string' => 'The name must be a valid string.',
            'applicant_name.max' => 'The name may not be greater than 255 characters.',
            'applicant_email.required' => 'The email field is required.',
            'applicant_email.email' => 'The email format is invalid.',
            'applicant_email.max' => 'The email may not be greater than 255 characters.',
            'applicant_phone.string' => 'The phone number must be a valid string.',
            'applicant_phone.max' => 'The phone number may not be greater than 20 characters.',
            'resume.required' => 'Please attach your resume.',
            'resume.file' => 'The resume must be a valid file.',
            'resume.mimes' => 'The resume must be a PDF, DOC, or DOCX file.',
            'resume.max' => 'The resume may not be greater than 5 MB.',
            'cover_letter.string' => 'The cover letter must be a valid string.',
            'cover_letter.max' => 'The cover letter may not be greater than 5000 characters.',
            'additional_info.string' => 'The additional information must be a valid string.',
            'additional_info.max' => 'The additional information may not be greater than 5000 characters.',
        ];
    }
}
