@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Application Management</h1>
                    <p class="text-muted">View and manage all job applications.</p>
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
            {{-- Applications Table --}}
            @if($applications->count() > 0)
                <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-secondary/50 border-b border-border">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Applicant</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Job Title</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Company</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Status</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Date</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Update Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @php
                                    $statusBadgeStyles = [
                                        'applied'     => 'bg-blue-100 text-blue-800',
                                        'reviewed'    => 'bg-yellow-100 text-yellow-800',
                                        'shortlisted' => 'bg-green-100 text-green-800',
                                        'rejected'    => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                @foreach($applications as $application)
                                    <tr class="hover:bg-secondary/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-foreground">{{ $application->applicant_name }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $application->jobListing->title ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $application->jobListing->company->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $statusBadgeStyles[$application->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $application->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $application->created_at->format('M j, Y') }}</td>
                                        <td class="px-6 py-4">
                                            <form method="POST" action="{{ route('admin.applications.updateStatus', $application) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select
                                                    name="status"
                                                    class="px-3 py-1.5 text-sm border border-border rounded-lg bg-background text-foreground focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                                                    aria-label="Update status for {{ $application->applicant_name }}"
                                                >
                                                    <option value="applied" {{ $application->status === 'applied' ? 'selected' : '' }}>Applied</option>
                                                    <option value="reviewed" {{ $application->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                                    <option value="shortlisted" {{ $application->status === 'shortlisted' ? 'selected' : '' }}>Shortlisted</option>
                                                    <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-primary text-white font-medium rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                                                >
                                                    Update
                                                </button>
                                            </form>
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
                        Page {{ $applications->currentPage() }} of {{ $applications->lastPage() }}
                        &middot; {{ $applications->total() }} {{ Str::plural('application', $applications->total()) }}
                    </p>
                    <div>
                        {{ $applications->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-card border border-border rounded-[var(--radius-card)] p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-foreground mb-2">No applications found</h2>
                    <p class="text-muted">There are no job applications to display.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
