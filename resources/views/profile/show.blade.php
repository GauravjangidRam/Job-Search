@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1100px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ============================================================= --}}
            {{-- Profile Header Card --}}
            {{-- ============================================================= --}}
            <section class="bg-card border border-border rounded-xl shadow-sm overflow-hidden mb-8">
                {{-- Cover/Banner --}}
                <div class="h-32 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent"></div>

                <div class="px-6 md:px-8 pb-6 md:pb-8 -mt-12">
                    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                        {{-- Avatar --}}
                        @php
                            $initials = collect(explode(' ', trim($user->name)))
                                ->filter()
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <div class="flex items-end gap-5">
                            <div class="relative flex-shrink-0">
                                @if(!empty($user->avatar_url))
                                    <img
                                        src="{{ asset('storage/' . ltrim($user->avatar_url, '/')) }}"
                                        alt="{{ $user->name }}'s avatar"
                                        class="h-24 w-24 rounded-full object-cover border-4 border-card bg-background shadow-sm"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                    <div
                                        class="h-24 w-24 rounded-full bg-primary text-white items-center justify-center text-2xl font-bold border-4 border-card shadow-sm"
                                        style="display:none;"
                                        aria-hidden="true"
                                    >{{ $initials ?: '?' }}</div>
                                @else
                                    <div
                                        class="h-24 w-24 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold border-4 border-card shadow-sm"
                                        aria-hidden="true"
                                    >{{ $initials ?: '?' }}</div>
                                @endif
                            </div>

                            <div class="pb-1">
                                <h1 class="text-2xl md:text-3xl font-bold text-foreground">{{ $user->name }}</h1>
                                <p class="text-muted text-sm capitalize">{{ $user->role ?? 'Job Seeker' }}</p>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <a
                                href="{{ route('profile.edit') }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                            >
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============================================================= --}}
            {{-- Profile Details Grid --}}
            {{-- ============================================================= --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                {{-- Left Column: Personal Info --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Contact Information --}}
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-primary"></i>
                            Contact Information
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-muted uppercase tracking-wide mb-1">Email</p>
                                <p class="text-sm text-foreground font-medium">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-muted uppercase tracking-wide mb-1">Phone</p>
                                <p class="text-sm text-foreground font-medium">
                                    {{ $user->phone ?: 'Not provided' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-muted uppercase tracking-wide mb-1">Member Since</p>
                                <p class="text-sm text-foreground font-medium">
                                    {{ $user->created_at?->format('F j, Y') }}
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- About / Bio --}}
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-4 h-4 text-primary"></i>
                            About Me
                        </h2>
                        @if(!empty($user->bio))
                            <p class="text-sm text-muted leading-relaxed whitespace-pre-line">{{ $user->bio }}</p>
                        @else
                            <p class="text-sm text-muted italic">No bio added yet. Tell employers about yourself!</p>
                        @endif
                    </section>

                    {{-- Quick Stats --}}
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <h2 class="text-base font-semibold text-foreground mb-4 flex items-center gap-2">
                            <i data-lucide="bar-chart-2" class="w-4 h-4 text-primary"></i>
                            Activity
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-secondary/50 rounded-lg">
                                <p class="text-2xl font-bold text-primary">{{ $applications->count() }}</p>
                                <p class="text-xs text-muted mt-1">Applications</p>
                            </div>
                            <div class="text-center p-3 bg-secondary/50 rounded-lg">
                                <p class="text-2xl font-bold text-primary">{{ $bookmarks->count() }}</p>
                                <p class="text-xs text-muted mt-1">Saved Jobs</p>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Right Column: Applications & Bookmarks --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Applications Section --}}
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-base font-semibold text-foreground flex items-center gap-2">
                                <i data-lucide="send" class="w-4 h-4 text-primary"></i>
                                My Applications
                            </h2>
                            <span class="text-xs text-muted bg-secondary px-2 py-1 rounded-full">{{ $applications->count() }} total</span>
                        </div>

                        @forelse($applications as $application)
                            @php
                                $listing = $application->jobListing;
                                $jobTitle = $listing?->title ?? 'Job no longer available';
                                $companyName = $listing?->company?->name
                                    ?? $listing?->company_name
                                    ?? 'Unknown Company';
                                $location = $listing?->location ?? '';

                                $statusStyles = [
                                    'applied'     => 'bg-gray-100 text-gray-700',
                                    'reviewed'    => 'bg-yellow-100 text-yellow-800',
                                    'shortlisted' => 'bg-emerald-100 text-emerald-800',
                                    'rejected'    => 'bg-red-100 text-red-800',
                                    'hired'       => 'bg-green-100 text-green-800',
                                ];
                                $status = $application->status ?? 'applied';
                                $badgeClass = $statusStyles[$status] ?? 'bg-blue-100 text-blue-800';
                            @endphp

                            <div
                                x-data="{ open: false }"
                                class="border border-border rounded-lg mb-3 last:mb-0 overflow-hidden hover:border-primary/30 transition-colors"
                            >
                                <button
                                    type="button"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                    class="w-full text-left p-4 flex items-start justify-between gap-4 hover:bg-secondary/30 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-inset transition-colors"
                                >
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-foreground truncate">{{ $jobTitle }}</h3>
                                        <p class="text-xs text-muted truncate mt-0.5">{{ $companyName }}@if($location) &middot; {{ $location }}@endif</p>
                                        <p class="text-xs text-muted mt-1">
                                            Applied {{ $application->created_at?->format('M d, Y') }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }} capitalize">
                                            {{ $status }}
                                        </span>
                                        <i
                                            data-lucide="chevron-down"
                                            class="w-4 h-4 text-muted transition-transform"
                                            :class="open ? 'rotate-180' : ''"
                                        ></i>
                                    </div>
                                </button>

                                {{-- Expanded details --}}
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-cloak
                                    class="px-4 pb-4 pt-0 border-t border-border"
                                >
                                    <div class="pt-3 space-y-3">
                                        @if($application->cover_letter)
                                            <div>
                                                <h4 class="text-xs font-semibold text-foreground mb-1">Cover Letter</h4>
                                                <p class="text-xs text-muted whitespace-pre-line leading-relaxed">{{ $application->cover_letter }}</p>
                                            </div>
                                        @endif
                                        @if($application->additional_info)
                                            <div>
                                                <h4 class="text-xs font-semibold text-foreground mb-1">Additional Information</h4>
                                                <p class="text-xs text-muted whitespace-pre-line leading-relaxed">{{ $application->additional_info }}</p>
                                            </div>
                                        @endif
                                        @if(!$application->cover_letter && !$application->additional_info)
                                            <p class="text-xs text-muted italic">No additional details provided.</p>
                                        @endif
                                        @if($listing)
                                            <div class="pt-2">
                                                <a href="{{ route('jobs.show', $listing->id) }}" class="text-xs text-primary hover:underline font-medium">
                                                    View job listing &rarr;
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <i data-lucide="file-search" class="w-10 h-10 text-muted mx-auto mb-3"></i>
                                <p class="text-foreground font-medium text-sm mb-1">No applications yet</p>
                                <p class="text-muted text-xs mb-4">Start exploring opportunities that match your skills.</p>
                                <a
                                    href="{{ route('jobs.index') }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-light transition-colors"
                                >
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                    Browse jobs
                                </a>
                            </div>
                        @endforelse
                    </section>

                    {{-- Saved Jobs Section --}}
                    <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-base font-semibold text-foreground flex items-center gap-2">
                                <i data-lucide="bookmark" class="w-4 h-4 text-primary"></i>
                                Saved Jobs
                            </h2>
                            <span class="text-xs text-muted bg-secondary px-2 py-1 rounded-full">{{ $bookmarks->count() }} saved</span>
                        </div>

                        @forelse($bookmarks as $bookmark)
                            @php
                                $bookmarkedListing = $bookmark->jobListing;
                                $bookmarkTitle = $bookmarkedListing?->title ?? 'Job no longer available';
                                $bookmarkCompany = $bookmarkedListing?->company?->name
                                    ?? $bookmarkedListing?->company_name
                                    ?? 'Unknown Company';
                                $bookmarkLocation = $bookmarkedListing?->location ?? '';
                            @endphp

                            <div class="flex items-center justify-between gap-4 p-3 border border-border rounded-lg mb-2 last:mb-0 hover:border-primary/30 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex-shrink-0 w-9 h-9 bg-primary/10 rounded-lg flex items-center justify-center">
                                        <i data-lucide="bookmark" class="w-4 h-4 text-primary"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-foreground truncate">{{ $bookmarkTitle }}</h3>
                                        <p class="text-xs text-muted truncate">{{ $bookmarkCompany }}@if($bookmarkLocation) &middot; {{ $bookmarkLocation }}@endif</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 flex-shrink-0">
                                    <p class="text-xs text-muted hidden sm:block">
                                        {{ $bookmark->created_at?->format('M d, Y') }}
                                    </p>
                                    @if($bookmarkedListing)
                                        <a href="{{ route('jobs.show', $bookmarkedListing->id) }}" class="text-xs text-primary hover:underline font-medium whitespace-nowrap">
                                            View &rarr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <i data-lucide="bookmark" class="w-10 h-10 text-muted mx-auto mb-3"></i>
                                <p class="text-foreground font-medium text-sm mb-1">No saved jobs</p>
                                <p class="text-muted text-xs">Bookmark jobs you're interested in to find them later.</p>
                            </div>
                        @endforelse
                    </section>
                </div>
            </div>

        </div>
    </div>
@endsection
