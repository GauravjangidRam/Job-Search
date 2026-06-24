@extends('layouts.app')

@section('title', $company->name)

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1100px] mx-auto pt-16 px-6 md:px-8 py-12">
        {{-- Back Link --}}
        <a href="{{ route('companies.index') }}" class="text-sm text-primary hover:underline mb-6 inline-block">&larr; Back to companies</a>
        {{-- Company Header Card --}}
        <section class="bg-card border border-border rounded-xl shadow-sm overflow-hidden mb-8">
            <div class="h-28 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent"></div>
            <div class="px-6 md:px-8 pb-6 md:pb-8 -mt-10">
                <div class="flex flex-col sm:flex-row sm:items-end gap-5">
                    {{-- Logo --}}
                    @php
                        $initials = collect(explode(' ', $company->name))
                            ->map(fn($word) => mb_substr($word, 0, 1))
                            ->take(2)
                            ->implode('');
                    @endphp
                    @if($company->logo_url)
                        <img
                            src="{{ $company->logo_url }}"
                            alt="{{ $company->name }} logo"
                            class="w-20 h-20 rounded-xl object-contain border-4 border-card bg-background p-2 shadow-sm"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div class="w-20 h-20 rounded-xl bg-primary text-white items-center justify-center text-xl font-bold border-4 border-card shadow-sm" style="display:none;" aria-hidden="true">{{ $initials }}</div>
                    @else
                        <div class="w-20 h-20 rounded-xl bg-primary text-white flex items-center justify-center text-xl font-bold border-4 border-card shadow-sm" aria-hidden="true">{{ $initials }}</div>
                    @endif

                    <div class="flex-1">
                        <h1 class="text-2xl md:text-3xl font-bold text-foreground">{{ $company->name }}</h1>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-muted">
                            @if($company->industry)
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                    {{ $company->industry }}
                                </span>
                            @endif
                            @if($company->employee_count)
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                    {{ number_format($company->employee_count) }} employees
                                </span>
                            @endif
                            @if($company->founded_year)
                                <span class="inline-flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    Founded {{ $company->founded_year }}
                                </span>
                            @endif
                            @if($company->is_hiring)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                    Actively Hiring
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($company->website_url)
                        <a
                            href="{{ $company->website_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-border text-foreground text-sm font-medium rounded-lg hover:bg-secondary transition-colors"
                        >
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            Website
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- About --}}
                @if($company->description)
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-foreground mb-3 flex items-center gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-primary"></i>
                            About {{ $company->name }}
                        </h2>
                        <p class="text-sm text-muted leading-relaxed whitespace-pre-line">{{ $company->description }}</p>
                    </section>
                @endif

                {{-- Culture --}}
                @if($company->culture)
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-foreground mb-3 flex items-center gap-2">
                            <i data-lucide="heart" class="w-4 h-4 text-primary"></i>
                            Culture
                        </h2>
                        <p class="text-sm text-muted leading-relaxed whitespace-pre-line">{{ $company->culture }}</p>
                    </section>
                @endif

                {{-- Open Positions --}}
                <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-semibold text-foreground flex items-center gap-2">
                            <i data-lucide="briefcase" class="w-4 h-4 text-primary"></i>
                            Open Positions
                        </h2>
                        <span class="text-xs text-muted bg-secondary px-2 py-1 rounded-full">{{ $jobListings->count() }} {{ Str::plural('job', $jobListings->count()) }}</span>
                    </div>

                    @forelse($jobListings as $job)
                        <a href="{{ $job->url }}" class="block border border-border rounded-lg p-4 mb-3 last:mb-0 hover:border-primary/30 hover:bg-secondary/30 transition-all">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-foreground">{{ $job->title }}</h3>
                                    <p class="text-xs text-muted mt-1">{{ $job->location }} &middot; {{ $job->job_type }} &middot; {{ $job->location_type }}</p>
                                </div>
                                <span class="text-xs font-medium text-foreground whitespace-nowrap">
                                    {{ $job->currency_symbol }}{{ number_format($job->salary_min) }} - {{ $job->currency_symbol }}{{ number_format($job->salary_max) }}
                                </span>
                            </div>
                            @if($job->skills && count($job->skills) > 0)
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach(array_slice($job->skills, 0, 5) as $skill)
                                        <span class="px-2 py-0.5 bg-secondary text-muted text-xs rounded-full">{{ $skill }}</span>
                                    @endforeach
                                    @if(count($job->skills) > 5)
                                        <span class="px-2 py-0.5 text-muted text-xs">+{{ count($job->skills) - 5 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="text-center py-8">
                            <i data-lucide="briefcase" class="w-8 h-8 text-muted mx-auto mb-2"></i>
                            <p class="text-muted text-sm">No open positions at the moment.</p>
                        </div>
                    @endforelse
                </section>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Company Metrics --}}
                @if($company->metrics && count($company->metrics) > 0)
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                            <i data-lucide="trending-up" class="w-4 h-4 text-primary"></i>
                            Key Metrics
                        </h2>
                        <div class="space-y-3">
                            @foreach($company->metrics as $label => $value)
                                <div class="flex items-center justify-between p-3 bg-secondary/50 rounded-lg">
                                    <span class="text-xs text-muted">{{ $label }}</span>
                                    <span class="text-sm font-semibold text-foreground">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Perks & Benefits --}}
                @if($company->perks && count($company->perks) > 0)
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                            <i data-lucide="gift" class="w-4 h-4 text-primary"></i>
                            Perks & Benefits
                        </h2>
                        <ul class="space-y-2">
                            @foreach(array_slice($company->perks, 0, 10) as $perk)
                                <li class="flex items-start gap-2 text-sm text-muted">
                                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5"></i>
                                    {{ $perk }}
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Quick Info --}}
                <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                    <h2 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-primary"></i>
                        Company Info
                    </h2>
                    <dl class="space-y-3 text-sm">
                        @if($company->industry)
                            <div>
                                <dt class="text-xs text-muted uppercase tracking-wide">Industry</dt>
                                <dd class="text-foreground font-medium mt-0.5">{{ $company->industry }}</dd>
                            </div>
                        @endif
                        @if($company->employee_count)
                            <div>
                                <dt class="text-xs text-muted uppercase tracking-wide">Company Size</dt>
                                <dd class="text-foreground font-medium mt-0.5">{{ number_format($company->employee_count) }} employees</dd>
                            </div>
                        @endif
                        @if($company->founded_year)
                            <div>
                                <dt class="text-xs text-muted uppercase tracking-wide">Founded</dt>
                                <dd class="text-foreground font-medium mt-0.5">{{ $company->founded_year }}</dd>
                            </div>
                        @endif
                        @if($company->website_url)
                            <div>
                                <dt class="text-xs text-muted uppercase tracking-wide">Website</dt>
                                <dd class="mt-0.5">
                                    <a href="{{ $company->website_url }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline font-medium text-xs break-all">
                                        {{ parse_url($company->website_url, PHP_URL_HOST) }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            </div>
        </div>
    </div>
@endsection
