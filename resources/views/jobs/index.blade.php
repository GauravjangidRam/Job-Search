@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1400px] mx-auto pt-16 px-6 md:px-8 py-12">
        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-foreground">Browse Jobs</h1>
            <p class="mt-2 text-muted">Find your next opportunity from {{ $jobs->total() }} available positions</p>
        </div>

        {{-- Filters & Search Section --}}
        <div x-data="{ showFilters: true }">
            {{-- Toggle Filter Panel Button --}}
            <div class="flex items-center justify-between mb-4">
                <button
                    type="button"
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center gap-2 text-sm font-medium text-foreground hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary rounded-card px-3 py-1.5"
                    :aria-expanded="showFilters.toString()"
                    aria-controls="filter-panel"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'">Hide Filters</span>
                    <svg class="w-4 h-4 transition-transform" :class="showFilters ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                @if(($filters['job_type'] ?? '') !== '' || ($filters['location_type'] ?? '') !== '' || ($filters['salary_min'] ?? '') !== '' || ($filters['salary_max'] ?? '') !== '' || ($filters['search'] ?? '') !== '' || ($filters['company_name'] ?? '') !== '')
                    <a
                        href="{{ route('jobs.index') }}"
                        class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Clear Filters
                    </a>
                @endif
            </div>

            {{-- Filter Panel --}}
            <form
                method="GET"
                action="{{ route('jobs.index') }}"
                id="filter-panel"
                x-show="showFilters"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
            >
                <div class="bg-card border border-border rounded-card p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                        {{-- Job Type Filter --}}
                        <div>
                            <label for="job_type" class="block text-sm font-medium text-foreground mb-1">Job Type</label>
                            <select
                                id="job_type"
                                name="job_type"
                                class="w-full rounded-card border border-border bg-background text-foreground px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                                <option value="">All Job Types</option>
                                <option value="Full-time" @selected(($filters['job_type'] ?? '') === 'Full-time')>Full-time</option>
                                <option value="Part-time" @selected(($filters['job_type'] ?? '') === 'Part-time')>Part-time</option>
                                <option value="Contract" @selected(($filters['job_type'] ?? '') === 'Contract')>Contract</option>
                                <option value="Freelance" @selected(($filters['job_type'] ?? '') === 'Freelance')>Freelance</option>
                                <option value="Internship" @selected(($filters['job_type'] ?? '') === 'Internship')>Internship</option>
                            </select>
                        </div>

                        {{-- Location Type Filter --}}
                        <div>
                            <label for="location_type" class="block text-sm font-medium text-foreground mb-1">Location Type</label>
                            <select
                                id="location_type"
                                name="location_type"
                                class="w-full rounded-card border border-border bg-background text-foreground px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                                <option value="">All Locations</option>
                                <option value="Remote" @selected(($filters['location_type'] ?? '') === 'Remote')>Remote</option>
                                <option value="On-site" @selected(($filters['location_type'] ?? '') === 'On-site')>On-site</option>
                                <option value="Hybrid" @selected(($filters['location_type'] ?? '') === 'Hybrid')>Hybrid</option>
                            </select>
                        </div>

                        {{-- Company Name Filter --}}
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-foreground mb-1">Company</label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="{{ $filters['company_name'] ?? '' }}"
                                placeholder="Filter by company name..."
                                class="w-full rounded-card border border-border bg-background text-foreground px-3 py-2 text-sm placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                        </div>

                        {{-- Salary Min --}}
                        <div>
                            <label for="salary_min" class="block text-sm font-medium text-foreground mb-1">Min Salary</label>
                            <input
                                type="number"
                                id="salary_min"
                                name="salary_min"
                                value="{{ $filters['salary_min'] ?? '' }}"
                                placeholder="e.g. 50000"
                                class="w-full rounded-card border border-border bg-background text-foreground px-3 py-2 text-sm placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                        </div>

                        {{-- Salary Max --}}
                        <div>
                            <label for="salary_max" class="block text-sm font-medium text-foreground mb-1">Max Salary</label>
                            <input
                                type="number"
                                id="salary_max"
                                name="salary_max"
                                value="{{ $filters['salary_max'] ?? '' }}"
                                placeholder="e.g. 150000"
                                class="w-full rounded-card border border-border bg-background text-foreground px-3 py-2 text-sm placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                        </div>

                        {{-- Search --}}
                        <div>
                            <label for="search" class="block text-sm font-medium text-foreground mb-1">Search</label>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ $filters['search'] ?? '' }}"
                                placeholder="Search by title, company, or description..."
                                class="w-full rounded-card border border-border bg-background text-foreground px-3 py-2 text-sm placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            >
                        </div>
                    </div>

                    {{-- Submit Row --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="submit"
                            class="px-6 py-2 bg-primary text-white text-sm font-medium rounded-card hover:bg-primary-light transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            Apply Filters
                        </button>
                        <a
                            href="{{ route('jobs.index') }}"
                            class="px-6 py-2 border border-border text-foreground text-sm font-medium rounded-card hover:bg-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            Clear All
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Job Listings --}}
        @if($jobs->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($jobs as $job)
                    <div class="relative bg-card border border-border rounded-card p-6 hover:shadow-lg hover:border-primary/30 transition-all duration-200 group">
                        {{-- Bookmark Button for Authenticated Seekers --}}
                        @auth
                            @if(auth()->user()->isSeeker())
                                <form
                                    method="POST"
                                    action="{{ route('bookmarks.toggle', $job) }}"
                                    class="absolute top-4 right-4 z-10"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="p-1.5 rounded-full text-muted hover:text-primary hover:bg-primary/10 transition-colors focus:outline-none focus:ring-2 focus:ring-primary"
                                        title="Toggle bookmark"
                                        aria-label="Toggle bookmark for {{ $job->title }}"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        @endauth

                        <a href="/jobs/{{ $job->id }}" class="block">
                            <div class="flex items-start justify-between mb-3 pr-8">
                                <h2 class="text-lg font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2">
                                    {{ $job->title }}
                                </h2>
                                <span class="ml-2 shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($job->job_type === 'Full-time') bg-green-100 text-green-800
                                    @elseif($job->job_type === 'Part-time') bg-blue-100 text-blue-800
                                    @elseif($job->job_type === 'Contract') bg-purple-100 text-purple-800
                                    @elseif($job->job_type === 'Freelance') bg-yellow-100 text-yellow-800
                                    @elseif($job->job_type === 'Internship') bg-pink-100 text-pink-800
                                    @endif
                                ">
                                    {{ $job->job_type }}
                                </span>
                            </div>

                            <p class="text-sm text-muted mb-2">{{ $job->company_name }}</p>

                            <div class="flex items-center text-sm text-muted mb-3">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $job->location }}
                            </div>

                            <div class="flex items-center text-sm font-medium text-foreground mb-4">
                                <svg class="w-4 h-4 mr-1 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                ${{ number_format($job->salary_min) }} - ${{ number_format($job->salary_max) }}
                            </div>

                            <div class="flex items-center justify-between pt-4 border-t border-border">
                                <span class="text-xs text-muted">
                                    {{ $job->created_at->diffForHumans() }}
                                </span>
                                <span class="text-xs text-muted px-2 py-1 bg-secondary rounded">
                                    {{ $job->location_type }}
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex items-center justify-between border-t border-border pt-6">
                <p class="text-sm text-muted">
                    Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}
                    &middot; {{ $jobs->total() }} {{ Str::plural('job', $jobs->total()) }} found
                </p>
                <div>
                    {{ $jobs->links() }}
                </div>
            </div>
        @else
            {{-- No Jobs Found --}}
            <div class="bg-card border border-border rounded-card p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h2 class="text-xl font-semibold text-foreground mb-2">No jobs found</h2>
                <p class="text-muted mb-6">We couldn't find any jobs matching your criteria. Try adjusting your filters or search term.</p>
                <a href="{{ route('jobs.index') }}" class="inline-flex items-center px-6 py-2 bg-primary text-white text-sm font-medium rounded-card hover:bg-primary-light transition-colors">
                    Clear all filters
                </a>
            </div>
        @endif
    </div>
@endsection
