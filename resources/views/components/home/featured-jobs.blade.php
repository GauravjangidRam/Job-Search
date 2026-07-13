@props(['featuredJobs'])

<section aria-labelledby="featured-jobs-heading">
    <div class="flex items-center justify-between mb-8">
        <div> 
            <h2 id="featured-jobs-heading" class="text-2xl md:text-3xl font-bold text-foreground">Latest Job Openings</h2>
            <p class="text-muted text-sm mt-1">Fresh opportunities added recently</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="text-sm text-primary hover:underline font-medium hidden sm:inline-flex items-center gap-1">
            View all jobs
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
    @if($featuredJobs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($featuredJobs as $job)
                @php
                    $companyInitials = collect(explode(' ', $job->company_name))
                        ->map(fn($word) => mb_substr($word, 0, 1))
                        ->take(2)
                        ->implode('');
                @endphp 
                <article class="bg-card border border-border rounded-xl p-5 flex flex-col hover:border-primary/30 hover:shadow-md transition-all duration-200 group">
                    {{-- Company Info --}}
                    <div class="flex items-start gap-3 mb-4">
                        @if($job->company_logo_url)
                            <img
                                src="{{ $job->company_logo_url }}"
                                alt="{{ $job->company_name }}"
                                class="w-10 h-10 rounded-lg object-contain border border-border bg-background p-1 flex-shrink-0"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary items-center justify-center text-xs font-bold flex-shrink-0" style="display:none;" aria-hidden="true">{{ $companyInitials }}</div>
                        @else
                            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0" aria-hidden="true">{{ $companyInitials }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-semibold text-foreground truncate group-hover:text-primary transition-colors" title="{{ $job->title }}">
                                {{ $job->title }}
                            </h3>
                            <p class="text-xs text-muted">{{ $job->company_name }} &middot; {{ $job->location }}</p>
                        </div>
                    </div> 
                    {{-- Tags --}}
                    <div class="flex flex-wrap gap-1.5 mb-3">
                        <span class="px-2 py-0.5 text-xs bg-secondary text-muted rounded-full">{{ $job->job_type }}</span>
                        <span class="px-2 py-0.5 text-xs bg-secondary text-muted rounded-full">{{ $job->location_type }}</span>
                        @if(!empty($job->skills))
                            @foreach(array_slice($job->skills, 0, 2) as $skill)
                                <span class="px-2 py-0.5 text-xs bg-secondary text-muted rounded-full">{{ $skill }}</span>
                            @endforeach
                        @endif
                    </div>
                    {{-- Salary --}}
                    <p class="text-sm font-semibold text-foreground mb-3">
                        {{ $job->currency_symbol }}{{ number_format($job->salary_min) }} - {{ $job->currency_symbol }}{{ number_format($job->salary_max) }}
                    </p>
                    {{-- Footer --}}
                    <div class="mt-auto flex items-center justify-between pt-3 border-t border-border">
                        <span class="text-xs text-muted">
                            {{ $job->created_at->diffForHumans() }}
                        </span> 
                        <a
                            href="{{ $job->url }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary text-white text-xs font-medium rounded-lg hover:bg-primary-light transition-colors"
                            aria-label="View {{ $job->title }}"
                        >
                            View Job
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div> 
                </article>
            @endforeach
        </div>
        <div class="mt-6 text-center sm:hidden">
            <a href="{{ route('jobs.index') }}" class="text-sm text-primary hover:underline font-medium">
                View all jobs &rarr;
            </a>
        </div>
    @else
        <div class="bg-card border border-border rounded-xl p-10 text-center">
            <i data-lucide="briefcase" class="w-10 h-10 text-muted mx-auto mb-3"></i>
            <p class="text-foreground font-medium text-sm mb-1">No jobs posted yet</p>
            <p class="text-muted text-xs">Job listings will appear here once employers start posting.</p>
        </div>
    @endif
</section> 