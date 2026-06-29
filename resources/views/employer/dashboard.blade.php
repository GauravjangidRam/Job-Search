@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1300px] mx-auto pt-16">
        <div class="py-10 px-6 md:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Sidebar --}}
                <x-employer.sidebar />
                {{-- Main Content --}}
                <div class="flex-1 min-w-0">
                    {{-- Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Dashboard</h1>
                            <p class="text-muted text-sm mt-1">Welcome back, {{ auth()->user()->name }}. Here's your hiring overview.</p>
                        </div>
                        <a
                            href="{{ route('employer.jobs.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Post a Job
                        </a>
                    </div>
                    {{-- Statistics Cards --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-card border border-border rounded-xl shadow-sm p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                    <i data-lucide="briefcase" class="w-4 h-4 text-emerald-600"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $totalActiveListings }}</p>
                            <p class="text-xs text-muted mt-1">Active Jobs</p>
                        </div>
                        <div class="bg-card border border-border rounded-xl shadow-sm p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                    <i data-lucide="file-text" class="w-4 h-4 text-blue-600"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $totalApplications }}</p>
                            <p class="text-xs text-muted mt-1">Total Applications</p>
                        </div>
                        <div class="bg-card border border-border rounded-xl shadow-sm p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                                    <i data-lucide="clock" class="w-4 h-4 text-yellow-600"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $applicationsByStatus['reviewed'] ?? 0 }}</p>
                            <p class="text-xs text-muted mt-1">Under Review</p>
                        </div>
                        <div class="bg-card border border-border rounded-xl shadow-sm p-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                    <i data-lucide="user-check" class="w-4 h-4 text-green-600"></i>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $applicationsByStatus['shortlisted'] ?? 0 }}</p>
                            <p class="text-xs text-muted mt-1">Shortlisted</p>
                        </div>
                    </div>

                    {{-- Company Quick Info --}}
                    @php $company = auth()->user()->company; @endphp
                    @if($company)
                        <div class="bg-card border border-border rounded-xl shadow-sm p-5 mb-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    @if($company->logo_url)
                                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="w-12 h-12 rounded-lg object-contain border border-border bg-background p-1">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">
                                            {{ mb_strtoupper(mb_substr($company->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="text-sm font-semibold text-foreground">{{ $company->name }}</h3>
                                        <p class="text-xs text-muted">{{ $company->industry ?? 'No industry set' }} &middot; {{ $company->employee_count ? number_format($company->employee_count) . ' employees' : 'Team size not set' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('employer.company.edit') }}" class="text-xs text-primary hover:underline font-medium">
                                    Edit Profile &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                        {{-- Applications by Status --}}
                        <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                            <h2 class="text-base font-semibold text-foreground mb-4">Applications by Status</h2>
                            @php
                                $statusStyles = [
                                    'applied'     => ['bg-gray-100 text-gray-700', 'bg-gray-200'],
                                    'reviewed'    => ['bg-yellow-100 text-yellow-800', 'bg-yellow-300'],
                                    'shortlisted' => ['bg-green-100 text-green-800', 'bg-green-400'],
                                    'rejected'    => ['bg-red-100 text-red-800', 'bg-red-300'],
                                ];
                                $maxCount = max(1, max($applicationsByStatus));
                            @endphp
                            <div class="space-y-3">
                                @foreach($applicationsByStatus as $statusKey => $count)
                                    @php $style = $statusStyles[$statusKey] ?? ['bg-blue-100 text-blue-800', 'bg-blue-300']; @endphp
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $style[0] }} w-24 justify-center">
                                            {{ $statusKey }}
                                        </span>
                                        <div class="flex-1 bg-secondary rounded-full h-2 overflow-hidden">
                                            <div class="{{ $style[1] }} h-full rounded-full transition-all" style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-foreground w-8 text-right">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        {{-- Recent Applications --}}
                        <section class="bg-card border border-border rounded-xl shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-base font-semibold text-foreground">Recent Applications</h2>
                                <a href="{{ route('employer.applications.index') }}" class="text-xs font-medium text-primary hover:underline">View all &rarr;</a>
                            </div>

                            @forelse($recentApplications as $item)
                                <div class="flex items-center justify-between gap-3 py-3 {{ !$loop->last ? 'border-b border-border' : '' }}">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-foreground truncate">{{ $item['applicant_name'] }}</p>
                                        <p class="text-xs text-muted truncate">{{ $item['job_title'] ?? 'Job no longer available' }}</p>
                                    </div>
                                    <p class="text-xs text-muted flex-shrink-0">{{ $item['date']?->format('M j') }}</p>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <i data-lucide="inbox" class="w-8 h-8 text-muted mx-auto mb-2"></i>
                                    <p class="text-muted text-sm">No applications yet.</p>
                                    <p class="text-muted text-xs mt-1">Applications will appear here once candidates apply to your jobs.</p>
                                </div>
                            @endforelse
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
