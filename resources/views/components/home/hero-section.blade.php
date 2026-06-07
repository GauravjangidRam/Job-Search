@props(['popularSearchTerms', 'heroJob' => null, 'stats' => []])

<div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
    {{-- Left Column --}}
    <div x-data="heroSearch">
        <h1 class="text-4xl md:text-5xl font-bold text-foreground leading-tight mb-4">
            Find work that moves you forward
        </h1>
        <p class="text-muted text-lg mb-8">
            Browse {{ $stats['jobs'] ?? 0 }} active jobs from {{ $stats['companies'] ?? 0 }} companies
        </p>

        {{-- Search Bar --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-3 p-3 bg-card border border-border rounded-xl shadow-sm">
                <div class="flex-1">
                    <label for="hero-job-title" class="sr-only">Job title</label>
                    <input
                        id="hero-job-title"
                        type="text"
                        placeholder="Job title or keyword"
                        maxlength="100"
                        x-model="jobTitle"
                        class="w-full px-4 py-3 bg-background border border-border rounded-lg text-foreground placeholder-muted focus:outline-none focus:ring-2 focus:ring-primary"
                        aria-describedby="hero-search-error"
                    >
                </div>
                <div class="flex-1">
                    <label for="hero-location" class="sr-only">Location</label>
                    <input
                        id="hero-location"
                        type="text"
                        placeholder="City or Remote"
                        maxlength="100"
                        x-model="location"
                        class="w-full px-4 py-3 bg-background border border-border rounded-lg text-foreground placeholder-muted focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                </div>
                <button
                    type="button"
                    @click="submit()"
                    class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 flex items-center gap-2"
                    aria-label="Search jobs"
                >
                    <i data-lucide="search" class="w-5 h-5"></i>
                    <span class="hidden sm:inline">Search</span>
                </button>
            </div>

            <p
                x-show="error"
                x-cloak
                id="hero-search-error"
                class="mt-2 text-sm text-red-600"
                role="alert"
                x-text="error"
            ></p>
        </div>

        {{-- Popular Search Terms --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-muted font-medium">Popular:</span>
            @foreach ($popularSearchTerms as $term)
                <button
                    type="button"
                    @click="setPopularTerm('{{ addslashes($term) }}')"
                    class="px-3 py-1.5 text-sm bg-secondary text-foreground border border-border rounded-full hover:bg-primary hover:text-white hover:border-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    {{ $term }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Right Column: Dynamic Job Card --}}
    <div class="hidden lg:block">
        @if($heroJob)
            @php
                $companyInitials = collect(explode(' ', $heroJob->company_name))
                    ->map(fn($word) => mb_substr($word, 0, 1))
                    ->take(2)
                    ->implode('');
                $applicationCount = $heroJob->applications()->count();
            @endphp
            <div class="bg-card border border-border rounded-xl p-6 shadow-lg">
                <div class="flex items-start gap-4 mb-4">
                    @if($heroJob->company_logo_url)
                        <img src="{{ $heroJob->company_logo_url }}" alt="{{ $heroJob->company_name }}" class="w-12 h-12 rounded-lg object-contain border border-border bg-background p-1">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">{{ $companyInitials }}</div>
                    @endif
                    <div>
                        <h3 class="font-semibold text-foreground text-lg">{{ $heroJob->title }}</h3>
                        <p class="text-muted text-sm">{{ $heroJob->company_name }} &middot; {{ $heroJob->location }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="px-2.5 py-1 text-xs font-medium bg-secondary text-muted rounded-full">{{ $heroJob->job_type }}</span>
                    <span class="px-2.5 py-1 text-xs font-medium bg-secondary text-muted rounded-full">{{ $heroJob->location_type }}</span>
                    @if(!empty($heroJob->skills))
                        @foreach(array_slice($heroJob->skills, 0, 2) as $skill)
                            <span class="px-2.5 py-1 text-xs font-medium bg-secondary text-muted rounded-full">{{ $skill }}</span>
                        @endforeach
                    @endif
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-foreground font-semibold">${{ number_format($heroJob->salary_min) }} - ${{ number_format($heroJob->salary_max) }}</span>
                    @if($applicationCount > 0)
                        <span class="text-xs text-muted">{{ $applicationCount }} {{ Str::plural('applicant', $applicationCount) }}</span>
                    @endif
                </div>
                <div class="mt-4 pt-4 border-t border-border flex items-center justify-between">
                    <span class="text-xs text-muted">Posted {{ $heroJob->created_at->diffForHumans() }}</span>
                    <a href="{{ route('jobs.show', $heroJob->hashed_id) }}" class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary-light transition-colors">
                        View Job
                    </a>
                </div>
            </div>
        @else
            <div class="bg-card border border-border rounded-xl p-8 text-center shadow-lg">
                <i data-lucide="briefcase" class="w-12 h-12 text-muted mx-auto mb-3"></i>
                <p class="text-foreground font-medium">Jobs will appear here</p>
                <p class="text-muted text-sm mt-1">Be the first to post a job!</p>
            </div>
        @endif
    </div>
</div>
