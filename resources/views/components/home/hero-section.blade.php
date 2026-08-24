@props(['popularSearchTerms', 'heroJob' => null, 'stats' => []])

<div class="lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
    {{-- Left Column --}}
    <div x-data="heroSearch">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary text-xs font-semibold rounded-full mb-4">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            <span>Next-Gen Talent Platform</span>
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-foreground tracking-tight leading-[1.15] mb-4">
            Find work that moves you forward
        </h1>
        <p class="text-muted text-lg md:text-xl mb-8 leading-relaxed">
            Browse <span class="text-foreground font-semibold">{{ number_format($stats['jobs'] ?? 0) }}</span> active jobs from <span class="text-foreground font-semibold">{{ number_format($stats['companies'] ?? 0) }}</span> top hiring companies
        </p>

        {{-- Search Bar --}}
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row gap-3 p-3 bg-card border border-border/80 rounded-2xl shadow-md hover:shadow-lg transition-shadow">
                <div class="flex-1">
                    <label for="hero-job-title" class="sr-only">Job title</label>
                    <input
                        id="hero-job-title"
                        type="text"
                        placeholder="Job title or keyword"
                        maxlength="100"
                        x-model="jobTitle"
                        class="w-full px-4 py-3 bg-background border border-border/70 rounded-xl text-foreground placeholder-muted focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
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
                        class="w-full px-4 py-3 bg-background border border-border/70 rounded-xl text-foreground placeholder-muted focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm"
                    >
                </div>
                <button
                    type="button"
                    @click="submit()"
                    class="px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-light transition-all shadow-sm flex items-center justify-center gap-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:scale-[0.98]"
                    aria-label="Search jobs"
                >
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span class="inline">Search</span>
                </button>
            </div>

            <p
                x-show="error"
                x-cloak
                id="hero-search-error"
                class="mt-2 text-sm text-red-600 font-medium"
                role="alert"
                x-text="error"
            ></p>
        </div>

        {{-- Popular Search Terms --}}
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-muted font-semibold uppercase tracking-wider">Popular:</span>
            @foreach ($popularSearchTerms as $term)
                <button
                    type="button"
                    @click="setPopularTerm('{{ addslashes($term) }}')"
                    class="px-3 py-1 text-xs font-medium bg-secondary text-foreground/90 border border-border/60 rounded-full hover:bg-primary hover:text-white hover:border-primary transition-all shadow-2xs focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
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
            <div class="bg-card border border-border/80 rounded-2xl p-7 shadow-xl relative overflow-hidden transition-all hover:border-primary/40">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="flex items-start gap-4 mb-5">
                    @if($heroJob->company_logo_url)
                        <img src="{{ $heroJob->company_logo_url }}" alt="{{ $heroJob->company_name }}" class="w-14 h-14 rounded-xl object-contain border border-border/60 bg-background p-1.5 shadow-2xs">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-base font-bold shadow-2xs">{{ $companyInitials }}</div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-semibold text-primary uppercase tracking-wide">Featured Opening</span>
                        <h3 class="font-bold text-foreground text-xl truncate mt-0.5">{{ $heroJob->title }}</h3>
                        <p class="text-muted text-sm">{{ $heroJob->company_name }} &middot; {{ $heroJob->location }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-5">
                    <span class="px-3 py-1 text-xs font-medium bg-secondary text-foreground/80 rounded-full">{{ $heroJob->job_type }}</span>
                    <span class="px-3 py-1 text-xs font-medium bg-secondary text-foreground/80 rounded-full">{{ $heroJob->location_type }}</span>
                    @if(!empty($heroJob->skills))
                        @foreach(array_slice($heroJob->skills, 0, 2) as $skill)
                            <span class="px-3 py-1 text-xs font-medium bg-primary/10 text-primary rounded-full">{{ $skill }}</span>
                        @endforeach
                    @endif
                </div>
                <div class="flex items-center justify-between py-2">
                    <div>
                        <span class="text-xs text-muted block">Salary Offer</span>
                        <span class="text-foreground font-bold text-lg">{{ $heroJob->currency_symbol }}{{ number_format($heroJob->salary_min) }} - {{ $heroJob->currency_symbol }}{{ number_format($heroJob->salary_max) }}</span>
                    </div>
                    @if($applicationCount > 0)
                        <span class="px-3 py-1 text-xs font-medium bg-emerald-500/10 text-emerald-600 rounded-full border border-emerald-500/20">{{ $applicationCount }} {{ Str::plural('applicant', $applicationCount) }}</span>
                    @endif 
                </div>
                <div class="mt-5 pt-5 border-t border-border/80 flex items-center justify-between">
                    <span class="text-xs text-muted">Posted {{ $heroJob->created_at->diffForHumans() }}</span>
                    <a href="{{ $heroJob->url }}" class="px-5 py-2.5 text-sm font-semibold bg-primary text-white rounded-xl hover:bg-primary-light transition-all shadow-sm flex items-center gap-1.5">
                        View Job
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div> 
        @else
            <div class="bg-card border border-border/80 rounded-2xl p-10 text-center shadow-lg">
                <i data-lucide="briefcase" class="w-12 h-12 text-muted mx-auto mb-3"></i>
                <p class="text-foreground font-semibold text-lg">Jobs will appear here</p>
                <p class="text-muted text-sm mt-1">Be the first to post a job on Job Hub!</p>
            </div>
        @endif
    </div>
</div>
