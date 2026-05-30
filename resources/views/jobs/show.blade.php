@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1400px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Back to Jobs Link --}}
            <a href="/jobs" class="inline-flex items-center text-muted hover:text-primary font-medium transition-colors duration-200 mb-8 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Jobs
            </a>

            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                {{-- Header Section --}}
                <div class="p-6 md:p-8 border-b border-border">
                    <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
                        {{-- Company Logo --}}
                        @if($job->company_logo_url)
                            <img
                                src="{{ $job->company_logo_url }}"
                                alt="{{ $job->company_name }} logo"
                                class="w-16 h-16 rounded-[var(--radius-card)] object-cover border border-border flex-shrink-0"
                            >
                        @else
                            <div class="w-16 h-16 rounded-[var(--radius-card)] bg-primary flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                <span class="text-white text-2xl font-bold">{{ strtoupper(substr($job->company_name, 0, 1)) }}</span>
                            </div>
                        @endif

                        {{-- Title and Company --}}
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl md:text-3xl font-bold text-foreground">{{ $job->title }}</h1>
                            <p class="text-lg text-muted mt-1">{{ $job->company_name }}</p>
                        </div>
                    </div>
                </div>

                {{-- Job Meta Information --}}
                <div class="p-6 md:p-8 border-b border-border">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Location --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="map-pin" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Location</p>
                                <p class="font-medium text-foreground">{{ $job->location }}</p>
                            </div>
                        </div>

                        {{-- Salary Range --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="dollar-sign" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Salary</p>
                                <p class="font-medium text-foreground">${{ number_format($job->salary_min) }} - ${{ number_format($job->salary_max) }}</p>
                            </div>
                        </div>

                        {{-- Job Type --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="briefcase" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Job Type</p>
                                <p class="font-medium text-foreground">{{ $job->job_type }}</p>
                            </div>
                        </div>

                        {{-- Location Type --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="monitor" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Work Mode</p>
                                <p class="font-medium text-foreground">{{ $job->location_type }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Posted Date --}}
                <div class="px-6 md:px-8 py-4 border-b border-border bg-secondary/50">
                    <p class="text-sm text-muted">
                        <i data-lucide="clock" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i>
                        Posted {{ $job->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Description --}}
                <div class="p-6 md:p-8 border-b border-border">
                    <h2 class="text-xl font-semibold text-foreground mb-4">Job Description</h2>
                    <div class="prose prose-stone max-w-none text-foreground/90 leading-relaxed whitespace-pre-line">{{ $job->description }}</div>
                </div>

                {{-- Skills --}}
                @if(!empty($job->skills))
                    <div class="p-6 md:p-8 border-b border-border">
                        <h2 class="text-xl font-semibold text-foreground mb-4">Required Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->skills as $skill)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-accent text-accent-dark">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Apply Now Button --}}
                <div class="p-6 md:p-8">
                    <a href="{{ route('jobs.apply', $job->id) }}" class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-[var(--radius-card)] hover:bg-primary-light transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                        Apply Now
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
