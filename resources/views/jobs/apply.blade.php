@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />
    <div class="max-w-[800px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Job context header --}}
            <div class="mb-8">
                <a href="{{ $job->url }}" class="text-sm text-primary hover:underline">&larr; Back to job details</a>
                <h1 class="text-2xl md:text-3xl font-bold text-foreground mt-3 mb-1">Apply for {{ $job->title }}</h1>
                <p class="text-muted">{{ $job->company_name }}@if(!empty($job->location)) &middot; {{ $job->location }}@endif</p>
            </div>
            {{-- Flash: success --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                    {{ session('success') }}
                </div>
            @endif 
            {{-- Flash: error --}}
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 font-medium" role="alert">
                    {{ session('error') }}
                </div>
            @endif 
            {{-- Validation Error Summary --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800" role="alert">
                    <p class="font-medium mb-2">Please correct the following errors:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if($hasApplied)
                {{-- Already applied: prominent message, form hidden (Requirement 4.5) --}}
                <div class="bg-card border border-border rounded-xl shadow-sm p-10 text-center">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 border-2 border-emerald-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-foreground mb-2">You've already applied to this job</h2>
                    <p class="text-muted text-sm leading-relaxed max-w-md mx-auto mb-8">
                        Your application for <span class="font-medium text-foreground">{{ $job->title }}</span> at {{ $job->company_name }} has already been submitted. You can track its status from your profile.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <a href="{{ route('profile.show') }}" class="w-full sm:w-auto px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors">
                            View my applications
                        </a>
                        <a href="{{ route('jobs.index') }}" class="w-full sm:w-auto px-6 py-2.5 border border-border text-foreground text-sm font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors">
                            Browse more jobs
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                    <form
                        method="POST"
                        action="{{ route('jobs.submitApplication', $job) }}"
                        enctype="multipart/form-data"
                        class="p-6 md:p-8"
                    >
                        @csrf
                        {{-- Applicant Name --}}
                        <div class="mb-6">
                            <label for="applicant_name" class="block text-sm font-medium mb-1">Full Name <span class="text-red-600">*</span></label>
                            <input
                                type="text"
                                id="applicant_name"
                                name="applicant_name"
                                value="{{ old('applicant_name', auth()->user()->name) }}"
                                required
                                maxlength="255"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('applicant_name') border-red-500 @enderror"
                            >
                            @error('applicant_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Applicant Email --}}
                        <div class="mb-6">
                            <label for="applicant_email" class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
                            <input
                                type="email"
                                id="applicant_email"
                                name="applicant_email"
                                value="{{ old('applicant_email', auth()->user()->email) }}"
                                required
                                maxlength="255"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('applicant_email') border-red-500 @enderror"
                            >
                            @error('applicant_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Applicant Phone --}}
                        <div class="mb-6">
                            <label for="applicant_phone" class="block text-sm font-medium mb-1">Phone</label>
                            <input
                                type="text"
                                id="applicant_phone"
                                name="applicant_phone"
                                value="{{ old('applicant_phone', auth()->user()->phone) }}"
                                maxlength="20"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('applicant_phone') border-red-500 @enderror"
                            >
                            @error('applicant_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Resume Upload --}}
                        <div class="mb-6">
                            <label for="resume" class="block text-sm font-medium mb-1">Resume <span class="text-red-600">*</span></label>
                            <input
                                type="file"
                                id="resume"
                                name="resume"
                                accept=".pdf,.doc,.docx"
                                required
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('resume') border-red-500 @enderror"
                            >
                            <p class="mt-1 text-xs text-muted">PDF, DOC, or DOCX up to 5 MB</p>
                            @error('resume')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Cover Letter --}}
                        <div class="mb-6">
                            <label for="cover_letter" class="block text-sm font-medium mb-1">Cover Letter</label>
                            <textarea
                                id="cover_letter"
                                name="cover_letter"
                                rows="6"
                                maxlength="5000"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('cover_letter') border-red-500 @enderror"
                            >{{ old('cover_letter') }}</textarea>
                            @error('cover_letter')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Additional Information --}}
                        <div class="mb-6">
                            <label for="additional_info" class="block text-sm font-medium mb-1">Additional Information</label>
                            <textarea
                                id="additional_info"
                                name="additional_info"
                                rows="4"
                                maxlength="5000"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('additional_info') border-red-500 @enderror"
                            >{{ old('additional_info') }}</textarea>
                            @error('additional_info')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3">
                            <a
                                href="{{ $job->url }}"
                                class="px-6 py-3 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                            >
                                Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
