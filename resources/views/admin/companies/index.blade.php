@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1300px] mx-auto pt-16">
        <div class="py-10 px-6 md:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <x-admin.sidebar />

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-foreground">Companies</h1>
                            <p class="text-muted text-sm mt-1">Review and manage registered companies</p>
                        </div>
                        @if($pendingCount > 0)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                {{ $pendingCount }} pending review
                            </span>
                        @endif
                    </div>

                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium text-sm" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Filters --}}
                    <div class="flex flex-wrap gap-2 mb-6">
                        <a href="{{ route('admin.companies.index') }}" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ !$statusFilter ? 'bg-primary text-white' : 'bg-secondary text-foreground hover:bg-border' }}">
                            All
                        </a>
                        <a href="{{ route('admin.companies.index', ['status' => 'pending']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $statusFilter === 'pending' ? 'bg-primary text-white' : 'bg-secondary text-foreground hover:bg-border' }}">
                            Pending
                        </a>
                        <a href="{{ route('admin.companies.index', ['status' => 'approved']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $statusFilter === 'approved' ? 'bg-primary text-white' : 'bg-secondary text-foreground hover:bg-border' }}">
                            Approved
                        </a>
                        <a href="{{ route('admin.companies.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ $statusFilter === 'rejected' ? 'bg-primary text-white' : 'bg-secondary text-foreground hover:bg-border' }}">
                            Rejected
                        </a>
                    </div>

                    {{-- Companies List --}}
                    @if($companies->count() > 0)
                        <div class="space-y-3">
                            @foreach($companies as $company)
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-emerald-100 text-emerald-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                    ];
                                    $badgeClass = $statusColors[$company->verification_status] ?? 'bg-gray-100 text-gray-800';
                                    $initials = collect(explode(' ', $company->name))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                                @endphp
                                <div class="bg-card border border-border rounded-xl p-5" x-data="{ showReject: false }">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div class="flex items-center gap-4 min-w-0">
                                            @if($company->logo_url)
                                                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="w-10 h-10 rounded-lg object-contain border border-border bg-background p-1">
                                            @else
                                                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-bold">{{ $initials }}</div>
                                            @endif
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <h3 class="text-sm font-semibold text-foreground truncate">{{ $company->name }}</h3>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }} capitalize">
                                                        {{ $company->verification_status }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-muted mt-0.5">
                                                    {{ $company->industry ?? 'No industry' }}
                                                    &middot; {{ $company->job_listings_count }} {{ Str::plural('job', $company->job_listings_count) }}
                                                    &middot; Registered {{ $company->created_at->format('M j, Y') }}
                                                    @if($company->employers->first())
                                                        &middot; Owner: {{ $company->employers->first()->name }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            @if($company->verification_status === 'pending')
                                                <form method="POST" action="{{ route('admin.companies.approve', $company) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Approve
                                                    </button>
                                                </form>
                                                <button type="button" @click="showReject = !showReject" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                    Reject
                                                </button>
                                            @elseif($company->verification_status === 'rejected')
                                                <form method="POST" action="{{ route('admin.companies.approve', $company) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-emerald-600 border border-emerald-200 rounded-lg hover:bg-emerald-50 transition-colors">
                                                        <i data-lucide="check" class="w-3 h-3"></i>
                                                        Approve
                                                    </button>
                                                </form>
                                            @elseif($company->verification_status === 'approved')
                                                <button type="button" @click="showReject = !showReject" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                    Revoke
                                                </button>
                                            @endif
                                            <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" onsubmit="return confirm('Delete this company? All their jobs will be removed too.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Rejection Reason Form --}}
                                    <div x-show="showReject" x-cloak class="mt-4 pt-4 border-t border-border">
                                        <form method="POST" action="{{ route('admin.companies.reject', $company) }}" class="flex items-end gap-3">
                                            @csrf
                                            <div class="flex-1">
                                                <label for="reason-{{ $company->id }}" class="block text-xs font-medium text-foreground mb-1">Rejection Reason</label>
                                                <input type="text" id="reason-{{ $company->id }}" name="rejection_reason" placeholder="e.g. Incomplete company information" class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                            </div>
                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition-colors">
                                                Confirm Reject
                                            </button>
                                        </form>
                                    </div>

                                    {{-- Show rejection reason if rejected --}}
                                    @if($company->verification_status === 'rejected' && $company->rejection_reason)
                                        <div class="mt-3 pt-3 border-t border-border">
                                            <p class="text-xs text-red-600"><span class="font-medium">Rejection reason:</span> {{ $company->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($companies->hasPages())
                            <div class="mt-6 flex items-center justify-between">
                                <p class="text-xs text-muted">{{ $companies->total() }} {{ Str::plural('company', $companies->total()) }}</p>
                                <div>{{ $companies->links() }}</div>
                            </div>
                        @endif
                    @else
                        <div class="bg-card border border-border rounded-xl p-10 text-center">
                            <i data-lucide="building-2" class="w-10 h-10 text-muted mx-auto mb-3"></i>
                            <p class="text-foreground font-medium text-sm">No companies found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
