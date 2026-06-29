@extends('layouts.app')

@section('title', 'Manage Job Alerts')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1100px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-foreground">Job Alerts</h1>
                <p class="mt-2 text-muted">Subscribe to email alerts and get notified daily when new matching jobs are posted.</p>
            </div>
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 font-medium" role="alert">
                    {{ session('error') }}
                </div>
            @endif
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Left Side: Create Alert Form --}}
                <div class="lg:col-span-1">
                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm sticky top-24">
                        <h2 class="text-lg font-bold text-foreground mb-4 flex items-center gap-2">
                            <i data-lucide="bell" class="w-5 h-5 text-primary"></i>
                            Create New Alert
                        </h2>
                        <form method="POST" action="{{ route('alerts.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="keywords" class="block text-sm font-medium text-foreground mb-1">Keywords</label>
                                <input
                                    type="text"
                                    id="keywords"
                                    name="keywords"
                                    placeholder="e.g. Laravel, React"
                                    maxlength="100"
                                    class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                                >
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-foreground mb-1">Location</label>
                                <input
                                    type="text"
                                    id="location"
                                    name="location"
                                    placeholder="e.g. Remote, Mumbai"
                                    maxlength="100"
                                    class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                                >
                            </div>
                            <div>
                                <label for="job_type" class="block text-sm font-medium text-foreground mb-1">Job Type</label>
                                <select
                                    id="job_type"
                                    name="job_type"
                                    class="w-full px-3 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                                >
                                    <option value="">Any Job Type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Freelance">Freelance</option>
                                    <option value="Internship">Internship</option>
                                </select>
                            </div>

                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light transition-colors focus:outline-none focus:ring-2 focus:ring-primary"
                            >
                                <i data-lucide="bell" class="w-4 h-4 mr-2"></i>
                                Subscribe Alert
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Right Side: Active Alerts --}}
                <div class="lg:col-span-2">
                    <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-foreground mb-5 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <i data-lucide="list" class="w-5 h-5 text-primary"></i>
                                Active Subscriptions
                            </span>
                            <span class="text-xs text-muted bg-secondary px-2.5 py-1 rounded-full font-semibold">{{ $alerts->count() }} active</span>
                        </h2>

                        @forelse($alerts as $alert)
                            <div class="flex items-center justify-between gap-4 p-4 border border-border rounded-lg mb-3 last:mb-0 hover:border-primary/20 transition-colors">
                                <div class="space-y-1.5 min-w-0">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($alert->keywords)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                                                <i data-lucide="search" class="w-3 h-3"></i>
                                                "{{ $alert->keywords }}"
                                            </span>
                                        @endif

                                        @if($alert->location)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                <i data-lucide="map-pin" class="w-3 h-3"></i>
                                                {{ $alert->location }}
                                            </span>
                                        @endif

                                        @if($alert->job_type)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                                <i data-lucide="briefcase" class="w-3 h-3"></i>
                                                {{ $alert->job_type }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-muted">
                                        Subscribed on {{ $alert->created_at->format('M d, Y') }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('alerts.destroy', $alert->id) }}" class="flex-shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-2 text-red-600 border border-border hover:bg-red-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
                                        title="Delete alert"
                                        onclick="return confirm('Are you sure you want to remove this job alert subscription?');"
                                    >
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i data-lucide="bell-off" class="w-12 h-12 text-muted mx-auto mb-3"></i>
                                <p class="text-foreground font-medium mb-1">No active job alerts</p>
                                <p class="text-muted text-sm">Create an alert on the left to start receiving matching job emails.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
