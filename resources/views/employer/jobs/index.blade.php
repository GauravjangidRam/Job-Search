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
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-foreground">Job Listings</h1>
                            <p class="text-muted text-sm mt-1">Manage your company's job postings.</p>
                        </div>
                        <a
                            href="{{ route('employer.jobs.create') }}"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Create Job
                        </a>
                    </div>

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium text-sm" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($jobs->count() > 0)
                        {{-- Job Cards --}}
                        <div class="space-y-3">
                            @foreach($jobs as $job)
                                <div class="bg-card border border-border rounded-xl shadow-sm p-5 hover:border-primary/30 transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-3 mb-1">
                                                <h3 class="text-sm font-semibold text-foreground truncate">{{ $job->title }}</h3>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0
                                                    @if($job->status === 'active') bg-emerald-100 text-emerald-800
                                                    @elseif($job->status === 'closed') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif
                                                ">
                                                    {{ ucfirst($job->status) }}
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-muted">
                                                <span class="inline-flex items-center gap-1">
                                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                    {{ $job->location }}
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i data-lucide="briefcase" class="w-3 h-3"></i>
                                                    {{ $job->job_type }}
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i data-lucide="monitor" class="w-3 h-3"></i>
                                                    {{ $job->location_type }}
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i data-lucide="dollar-sign" class="w-3 h-3"></i>
                                                    ${{ number_format($job->salary_min) }} - ${{ number_format($job->salary_max) }}
                                                </span>
                                                <span class="inline-flex items-center gap-1">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    {{ $job->created_at->format('M j, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <a
                                                href="{{ route('employer.jobs.edit', $job) }}"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-foreground border border-border rounded-lg hover:bg-secondary transition-colors"
                                            >
                                                <i data-lucide="pencil" class="w-3 h-3"></i>
                                                Edit
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('employer.jobs.destroy', $job) }}"
                                                onsubmit="return confirm('Delete this job listing? This cannot be undone.');"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
                                                >
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($jobs->hasPages())
                            <div class="flex items-center justify-between pt-6 mt-4 border-t border-border">
                                <p class="text-xs text-muted">
                                    {{ $jobs->total() }} {{ Str::plural('listing', $jobs->total()) }} &middot; Page {{ $jobs->currentPage() }} of {{ $jobs->lastPage() }}
                                </p>
                                <div>
                                    {{ $jobs->links() }}
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Empty State --}}
                        <div class="bg-card border border-border rounded-xl p-12 text-center">
                            <i data-lucide="briefcase" class="w-12 h-12 text-muted mx-auto mb-3"></i>
                            <h2 class="text-lg font-semibold text-foreground mb-2">No job listings yet</h2>
                            <p class="text-muted text-sm mb-6">Get started by creating your first job posting.</p>
                            <a
                                href="{{ route('employer.jobs.create') }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light transition-colors"
                            >
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                Create Job
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
