@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Job Listings Management</h1>
                    <p class="text-muted">Manage all job listings on the platform.</p>
                </div>
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                >
                    Back to Dashboard
                </a>
            </div>
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            {{-- Filter Bar --}}
            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-4 md:p-6 mb-6">
                <form method="GET" action="{{ route('admin.jobs.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-4">
                    <div class="sm:w-48">
                        <label for="status" class="block text-sm font-medium text-foreground mb-1">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                        >
                            <option value="">All Statuses</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $statusFilter === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                    >
                        Filter
                    </button>
                </form>
            </div>
            {{-- Job Listings Table --}}
            @if($listings->count() > 0)
                <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-secondary/50 border-b border-border">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Title</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Company</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Status</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Created</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @php
                                    $statusBadgeStyles = [
                                        'draft'  => 'bg-gray-100 text-gray-800',
                                        'active' => 'bg-green-100 text-green-800',
                                        'closed' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                @foreach($listings as $listing)
                                    <tr class="hover:bg-secondary/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-foreground">{{ $listing->title }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $listing->company->name ?? $listing->company_name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $statusBadgeStyles[$listing->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $listing->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $listing->created_at->format('M j, Y') }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                {{-- Approve --}}
                                                <form method="POST" action="{{ route('admin.jobs.approve', $listing) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-2 focus:outline-offset-2 focus:outline-green-600 transition-colors"
                                                    >
                                                        Approve
                                                    </button>
                                                </form>

                                                {{-- Reject --}}
                                                <form method="POST" action="{{ route('admin.jobs.reject', $listing) }}">
                                                    @csrf
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-2 focus:outline-offset-2 focus:outline-yellow-600 transition-colors"
                                                    >
                                                        Reject
                                                    </button>
                                                </form>

                                                {{-- Delete --}}
                                                <form method="POST" action="{{ route('admin.jobs.destroy', $listing) }}" onsubmit="return confirm('Are you sure you want to delete this job listing? All associated applications will also be removed.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-2 focus:outline-offset-2 focus:outline-red-600 transition-colors"
                                                    >
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="flex items-center justify-between border-t border-border pt-6 mt-6">
                    <p class="text-sm text-muted">
                        Page {{ $listings->currentPage() }} of {{ $listings->lastPage() }}
                        &middot; {{ $listings->total() }} {{ Str::plural('listing', $listings->total()) }}
                    </p>
                    <div>
                        {{ $listings->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-card border border-border rounded-[var(--radius-card)] p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-foreground mb-2">No job listings found</h2>
                    <p class="text-muted">Try adjusting your filter criteria.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
