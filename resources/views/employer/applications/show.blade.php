@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    @php
        $status = $application->status ?? 'applied';
        $statusStyles = [
            'applied'     => 'bg-gray-100 text-gray-700',
            'reviewed'    => 'bg-yellow-100 text-yellow-800',
            'shortlisted' => 'bg-green-100 text-green-800',
            'rejected'    => 'bg-red-100 text-red-800',
        ];
        $badgeClass = $statusStyles[$status] ?? 'bg-blue-100 text-blue-800';
    @endphp

    <div class="max-w-[900px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Back link --}}
            <div class="mb-6">
                <a href="{{ route('employer.applications.index') }}" class="text-sm text-primary hover:underline">&larr; Back to applications</a>
            </div>

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-1">{{ $application->applicant_name }}</h1>
                    <p class="text-muted">Applied for {{ $application->jobListing->title ?? 'Job no longer available' }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize flex-shrink-0 {{ $badgeClass }}">
                    {{ $status }}
                </span>
            </div>

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                    {{ session('success') }}
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

            {{-- Applicant Details --}}
            <section class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-6 md:p-8 mb-8">
                <h2 class="text-lg font-semibold text-foreground mb-4">Applicant Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted mb-1">Name</dt>
                        <dd class="text-foreground">{{ $application->applicant_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted mb-1">Email</dt>
                        <dd class="text-foreground">
                            <a href="mailto:{{ $application->applicant_email }}" class="text-primary hover:underline">{{ $application->applicant_email }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted mb-1">Phone</dt>
                        <dd class="text-foreground">{{ $application->applicant_phone ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted mb-1">Job</dt>
                        <dd class="text-foreground">{{ $application->jobListing->title ?? 'Job no longer available' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-muted mb-1">Date Applied</dt>
                        <dd class="text-foreground">{{ $application->created_at?->format('M j, Y') }}</dd>
                    </div>
                </dl>

                @if(!empty($application->resume_path))
                    <div class="mt-6 pt-6 border-t border-border">
                        <a
                            href="{{ route('employer.applications.resume', $application) }}"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-medium rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Resume
                        </a>
                    </div>
                @endif
            </section>

            {{-- Cover Letter --}}
            <section class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-6 md:p-8 mb-8">
                <h2 class="text-lg font-semibold text-foreground mb-3">Cover Letter</h2>
                <p class="text-muted whitespace-pre-line">{{ $application->cover_letter ?: 'No cover letter provided.' }}</p>
            </section>

            {{-- Additional Information --}}
            <section class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-6 md:p-8 mb-8">
                <h2 class="text-lg font-semibold text-foreground mb-3">Additional Information</h2>
                <p class="text-muted whitespace-pre-line">{{ $application->additional_info ?: 'No additional information provided.' }}</p>
            </section>

            {{-- Status Update --}}
            <section class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-6 md:p-8">
                <h2 class="text-lg font-semibold text-foreground mb-4">Update Status</h2>
                <form method="POST" action="{{ route('employer.applications.updateStatus', $application) }}">
                    @csrf
                    @method('PATCH')
                    <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                        <div class="flex-1">
                            <label for="status" class="block text-sm font-medium mb-1">Status</label>
                            <select
                                id="status"
                                name="status"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('status') border-red-500 @enderror"
                            >
                                <option value="applied" @selected($status === 'applied')>Applied</option>
                                <option value="reviewed" @selected($status === 'reviewed')>Reviewed</option>
                                <option value="shortlisted" @selected($status === 'shortlisted')>Shortlisted</option>
                                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button
                            type="submit"
                            class="px-6 py-2 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Update Status
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
