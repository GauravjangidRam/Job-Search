@props(['featuredJobs'])

<section aria-labelledby="featured-jobs-heading">
    <div class="flex items-center justify-between mb-8">
        <div> 
            <h2 id="featured-jobs-heading" class="text-2xl md:text-3xl font-extrabold text-foreground tracking-tight">Latest Job Openings</h2>
            <p class="text-muted text-sm mt-1">Fresh opportunities added recently</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="text-sm text-primary hover:text-primary-light font-semibold hidden sm:inline-flex items-center gap-1.5 transition-colors group">
            View all jobs
            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
        </a>
    </div>
    @if($featuredJobs->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($featuredJobs as $job)
                @php
                    $companyInitials = collect(explode(' ', $job->company_name))
                        ->map(fn($word) => mb_substr($word, 0, 1))
                        ->take(2)
                        ->implode('');
                @endphp 
                <article class="bg-card border border-border/80 rounded-2xl p-6 flex flex-col hover:border-primary/40 hover:shadow-lg transition-all duration-200 group hover:-translate-y-0.5 relative">
                    {{-- Company Info --}}
                    <div class="flex items-start gap-4 mb-4">
                        @if($job->company_logo_url)
                            <img
                                src="{{ $job->company_logo_url }}"
                                alt="{{ $job->company_name }}"
                                class="w-12 h-12 rounded-xl object-contain border border-border/60 bg-background p-1 flex-shrink-0 shadow-2xs"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary items-center justify-center text-xs font-bold flex-shrink-0 shadow-2xs" style="display:none;" aria-hidden="true">{{ $companyInitials }}</div>
                        @else
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-bold flex-shrink-0 shadow-2xs" aria-hidden="true">{{ $companyInitials }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-bold text-foreground truncate group-hover:text-primary transition-colors" title="{{ $job->title }}">
                                {{ $job->title }}
                            </h3>
                            <p class="text-xs text-muted font-medium mt-0.5">{{ $job->company_name }} &middot; {{ $job->location }}</p>
                        </div>
                    </div> 
                    {{-- Tags --}}
                    <div class="flex flex-wrap gap-1.5 mb-4">
                        <span class="px-2.5 py-1 text-xs font-medium bg-secondary text-foreground/80 rounded-full">{{ $job->job_type }}</span>
                        <span class="px-2.5 py-1 text-xs font-medium bg-secondary text-foreground/80 rounded-full">{{ $job->location_type }}</span>
                        @if(!empty($job->skills))
                            @foreach(array_slice($job->skills, 0, 2) as $skill)
                                <span class="px-2.5 py-1 text-xs font-medium bg-primary/10 text-primary rounded-full">{{ $skill }}</span>
                            @endforeach
                        @endif
                    </div>
                    {{-- Salary --}}
                    <p class="text-sm font-bold text-foreground mb-4">
                        {{ $job->currency_symbol }}{{ number_format($job->salary_min) }} - {{ $job->currency_symbol }}{{ number_format($job->salary_max) }}
                    </p>
                    {{-- Footer --}}
                    <div class="mt-auto flex items-center justify-between pt-4 border-t border-border/70">
                        <span class="text-xs text-muted">
                            {{ $job->created_at->diffForHumans() }}
                        </span> 
                        <a
                            href="{{ $job->url }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-primary-light transition-all shadow-xs"
                            aria-label="View {{ $job->title }}"
                        >
                            View Job
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div> 
                </article>
            @endforeach
        </div>
        <div class="mt-6 text-center sm:hidden">
            <a href="{{ route('jobs.index') }}" class="text-sm text-primary hover:underline font-semibold">
                View all jobs &rarr;
            </a>
        </div>
    @else
        <div class="bg-card border border-border/80 rounded-2xl p-10 text-center shadow-sm">
            <i data-lucide="briefcase" class="w-10 h-10 text-muted mx-auto mb-3"></i>
            <p class="text-foreground font-semibold text-base mb-1">No jobs posted yet</p>
            <p class="text-muted text-xs">Job listings will appear here once employers start posting.</p>
        </div>
    @endif
</section> 