@extends('layouts.app')

@section('title', 'Browse Companies')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1400px] mx-auto pt-16 px-6 md:px-8 py-12"
         x-data="{
            industryFilter: '',
            hiringOnly: false,
            companies: @js($companies->getCollection()->map(fn($c) => ['id' => $c->id, 'industry' => $c->industry ?? '', 'is_hiring' => (bool) $c->is_hiring])->values()),
            get visibleCount() {
                return this.companies.filter(c => this.matchesFilter(c.industry, c.is_hiring)).length;
            },
            matchesFilter(industry, isHiring) {
                if (this.industryFilter && industry !== this.industryFilter) return false;
                if (this.hiringOnly && !isHiring) return false;
                return true;
            },
            clearFilters() {
                this.industryFilter = '';
                this.hiringOnly = false;
            },
            get hasActiveFilters() {
                return this.industryFilter !== '' || this.hiringOnly;
            }
         }">

        {{-- Page Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-foreground">Companies</h1>
            <p class="mt-2 text-muted">Browse {{ $companies->total() }} companies hiring on our platform</p>
        </div> 

        {{-- Filters --}}
        <div class="mb-8 flex flex-wrap items-center gap-4">
            <div>
                <label for="industry-filter" class="sr-only">Filter by industry</label>
                <select
                    id="industry-filter"
                    x-model="industryFilter"
                    class="rounded-lg border border-border bg-background text-foreground px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                >
                    <option value="">All Industries</option>
                    @php
                        $industries = $companies->getCollection()->pluck('industry')->filter()->unique()->sort()->values();
                    @endphp
                    @foreach($industries as $industry)
                        <option value="{{ $industry }}">{{ $industry }}</option>
                    @endforeach
                </select>
            </div>

            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    type="checkbox"
                    x-model="hiringOnly"
                    class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
                >
                <span class="text-sm text-foreground">Hiring only</span>
            </label>

            <button
                x-show="hasActiveFilters"
                x-on:click="clearFilters()"
                x-cloak
                class="text-sm text-primary hover:underline font-medium"
            >
                Clear filters
            </button>
        </div>

        {{-- Company Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($companies as $company)
                <div
                    x-show="matchesFilter('{{ $company->industry ?? '' }}', {{ $company->is_hiring ? 'true' : 'false' }})"
                    class="bg-card border border-border rounded-xl shadow-sm p-6 flex flex-col hover:border-primary/30 hover:shadow-md transition-all duration-200"
                >
                    {{-- Company Header --}}
                    <div class="flex items-center gap-4 mb-4">
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
                                class="w-12 h-12 rounded-lg object-contain border border-border bg-background p-1"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                            >
                            <div class="w-12 h-12 rounded-lg bg-primary text-white items-center justify-center text-sm font-bold" style="display:none;" aria-hidden="true">{{ $initials }}</div>
                        @else
                            <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm font-bold" aria-hidden="true">{{ $initials }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-semibold text-foreground truncate">{{ $company->name }}</h2>
                            @if($company->industry)
                                <p class="text-sm text-muted">{{ $company->industry }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Description --}}
                    @if($company->description)
                        <p class="text-sm text-muted line-clamp-2 mb-4">{{ $company->description }}</p>
                    @endif 

                    {{-- Meta Info --}}
                    <div class="flex items-center gap-4 text-xs text-muted mb-4">
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
                    </div>

                    {{-- Footer --}}
                    <div class="mt-auto flex items-center justify-between pt-4 border-t border-border">
                        @if($company->is_hiring)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                Hiring
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary text-muted">
                                Not hiring
                            </span>
                        @endif
                        <a href="{{ route('companies.show', $company->slug) }}" class="text-sm text-primary hover:underline font-medium">
                            View Profile &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-card border border-border rounded-xl p-12 text-center">
                    <i data-lucide="building-2" class="w-12 h-12 text-muted mx-auto mb-3"></i>
                    <p class="text-foreground font-medium mb-1">No companies found</p>
                    <p class="text-muted text-sm">Companies will appear here once they register.</p>
                </div>
            @endforelse
        </div> 
        {{-- Empty State for Filters --}}
        @if($companies->count() > 0)
            <div
                x-show="hasActiveFilters && visibleCount === 0"
                x-cloak
                class="flex flex-col items-center justify-center py-12 px-6"
            >
                <p class="text-muted text-center mb-4">No companies match the selected filters.</p>
                <button
                    x-on:click="clearFilters()"
                    class="px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-light transition-colors"
                >
                    Clear all filters
                </button>
            </div>
        @endif

        {{-- Pagination --}}
        @if($companies->hasPages())
            <div class="mt-8 flex items-center justify-between border-t border-border pt-6">
                <p class="text-sm text-muted">
                    Page {{ $companies->currentPage() }} of {{ $companies->lastPage() }}
                </p>
                <div>
                    {{ $companies->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection