@props(['companies'])

<section aria-labelledby="company-showcase-heading">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 id="company-showcase-heading" class="text-2xl md:text-3xl font-extrabold text-foreground tracking-tight">Top Companies Hiring</h2>
            <p class="text-muted text-sm mt-1">Discover great places to work</p>
        </div>
        <a href="{{ route('companies.index') }}" class="text-sm text-primary hover:text-primary-light font-semibold hidden sm:inline-flex items-center gap-1.5 transition-colors group">
            View all companies
            <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
        </a>
    </div>
    @if($companies->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($companies as $company)
                @php
                    $initials = collect(explode(' ', $company->name))
                        ->map(fn($word) => mb_substr($word, 0, 1))
                        ->take(2)
                        ->implode('');
                    $jobCount = $company->jobListings()->where('status', 'active')->count();
                @endphp 
                <a href="{{ route('companies.show', $company->slug) }}" class="block bg-card border border-border/80 rounded-2xl p-6 hover:border-primary/40 hover:shadow-lg transition-all duration-200 group hover:-translate-y-0.5 relative">
                    <div class="flex items-center gap-4 mb-4">
                        @if($company->logo_url)
                            <img
                                src="{{ $company->logo_url }}"
                                alt="{{ $company->name }} logo"
                                class="w-12 h-12 rounded-xl object-contain border border-border/60 bg-background p-1 shadow-2xs"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary items-center justify-center text-xs font-bold shadow-2xs" style="display:none;" aria-hidden="true">{{ $initials }}</div>
                        @else
                            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shadow-2xs" aria-hidden="true">{{ $initials }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-bold text-foreground truncate group-hover:text-primary transition-colors">{{ $company->name }}</h3>
                            <p class="text-xs text-muted font-medium">{{ $company->industry ?? 'Technology' }}</p>
                        </div>
                    </div> 
                    @if($company->description)
                        <p class="text-xs text-muted leading-relaxed line-clamp-2 mb-4">{{ Str::limit($company->description, 100) }}</p>
                    @endif
                    <div class="flex items-center justify-between pt-4 border-t border-border/70">
                        <div class="flex items-center gap-3 text-xs text-muted font-medium">
                            @if($company->employee_count)
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    {{ number_format($company->employee_count) }}
                                </span>
                            @endif
                            @if($jobCount > 0)
                                <span class="inline-flex items-center gap-1 text-primary font-semibold">
                                    <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                    {{ $jobCount }} {{ Str::plural('job', $jobCount) }}
                                </span>
                            @endif
                        </div> 
                        @if($company->is_hiring)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">
                                Hiring
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div> 
        <div class="mt-6 text-center sm:hidden">
            <a href="{{ route('companies.index') }}" class="text-sm text-primary hover:underline font-semibold">
                View all companies &rarr;
            </a>
        </div>
    @else
        <div class="bg-card border border-border/80 rounded-2xl p-10 text-center shadow-sm">
            <i data-lucide="building-2" class="w-10 h-10 text-muted mx-auto mb-3"></i>
            <p class="text-muted text-sm font-medium">Companies will appear here once they register and start hiring.</p>
        </div>
    @endif
</section>