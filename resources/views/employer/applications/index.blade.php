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
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-foreground">Applications</h1>
                        <p class="text-muted text-sm mt-1">Review and manage applications for your job listings.</p>
                    </div>
                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium text-sm" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    {{-- Filters --}}
                    <form method="GET" action="{{ route('employer.applications.index') }}" class="bg-card border border-border rounded-xl shadow-sm p-4 mb-6">
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1 min-w-[150px]">
                                <label for="status" class="block text-xs font-medium text-foreground mb-1">Status</label>
                                <select
                                    id="status"
                                    name="status"
                                    class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                >
                                    <option value="" @selected($statusFilter === null || $statusFilter === '')>All statuses</option>
                                    <option value="applied" @selected($statusFilter === 'applied')>Applied</option>
                                    <option value="reviewed" @selected($statusFilter === 'reviewed')>Reviewed</option>
                                    <option value="shortlisted" @selected($statusFilter === 'shortlisted')>Shortlisted</option>
                                    <option value="rejected" @selected($statusFilter === 'rejected')>Rejected</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[150px]">
                                <label for="job_listing_id" class="block text-xs font-medium text-foreground mb-1">Job Listing</label>
                                <select
                                    id="job_listing_id"
                                    name="job_listing_id"
                                    class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                >
                                    <option value="" @selected($jobListingFilter === null)>All listings</option>
                                    @foreach($jobListings as $jobListing)
                                        <option value="{{ $jobListing->id }}" @selected((int) $jobListingFilter === (int) $jobListing->id)>
                                            {{ $jobListing->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div> 
                            <div class="flex items-center gap-2">
                                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-light transition-colors">
                                    Filter
                                </button>
                                @if($jobListingFilter !== null || ($statusFilter !== null && $statusFilter !== ''))
                                    <a href="{{ route('employer.applications.index') }}" class="px-4 py-2 border border-border text-foreground text-sm font-medium rounded-lg hover:bg-secondary transition-colors">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                    @php
                        $statusStyles = [
                            'applied'     => 'bg-gray-100 text-gray-700',
                            'reviewed'    => 'bg-yellow-100 text-yellow-800',
                            'shortlisted' => 'bg-green-100 text-green-800',
                            'rejected'    => 'bg-red-100 text-red-800',
                        ];
                    @endphp

                    @if($applications->count() > 0)
                        <div class="space-y-3">
                            @foreach($applications as $application)
                                @php
                                    $status = $application->status ?? 'applied';
                                    $badgeClass = $statusStyles[$status] ?? 'bg-blue-100 text-blue-800';
                                @endphp
                                <div class="bg-card border border-border rounded-xl shadow-sm p-4 hover:border-primary/30 transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-sm font-semibold text-foreground truncate">{{ $application->applicant_name }}</h3>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize {{ $badgeClass }}">
                                                    {{ $status }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-muted">
                                                Applied for <span class="font-medium text-foreground">{{ $application->jobListing->title ?? 'Job removed' }}</span>
                                                &middot; {{ $application->created_at?->format('M j, Y') }}
                                            </p>
                                        </div>
                                        <a
                                            href="{{ route('employer.applications.show', $application) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary/10 transition-colors flex-shrink-0"
                                        >
                                            View Details
                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($applications->hasPages())
                            <div class="flex items-center justify-between pt-6 mt-4 border-t border-border">
                                <p class="text-xs text-muted">
                                    {{ $applications->total() }} {{ Str::plural('application', $applications->total()) }}
                                </p>
                                <div>
                                    {{ $applications->links() }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="bg-card border border-border rounded-xl p-10 text-center">
                            <i data-lucide="file-text" class="w-10 h-10 text-muted mx-auto mb-3"></i>
                            <h2 class="text-lg font-semibold text-foreground mb-2">No applications found</h2>
                            <p class="text-muted text-sm">
                                @if($jobListingFilter !== null || ($statusFilter !== null && $statusFilter !== ''))
                                    No applications match the selected filters.
                                @else
                                    Applications will appear here as candidates apply to your jobs.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
