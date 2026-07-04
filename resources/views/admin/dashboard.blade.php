@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1300px] mx-auto pt-16">
        <div class="py-10 px-6 md:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <x-admin.sidebar />

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-foreground mb-1">Admin Dashboard</h1>
                    <p class="text-muted text-sm mb-8">Platform overview and activity</p>
                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-card border border-border rounded-xl p-5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center mb-2">
                                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $totalUsers }}</p>
                            <p class="text-xs text-muted mt-1">Total Users</p>
                        </div> 
                        <div class="bg-card border border-border rounded-xl p-5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center mb-2">
                                <i data-lucide="building-2" class="w-4 h-4 text-emerald-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $totalCompanies }}</p>
                            <p class="text-xs text-muted mt-1">Companies</p>
                        </div>
                        <div class="bg-card border border-border rounded-xl p-5">
                            <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center mb-2">
                                <i data-lucide="briefcase" class="w-4 h-4 text-purple-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $totalListings }}</p>
                            <p class="text-xs text-muted mt-1">Job Listings</p>
                        </div>
                        <div class="bg-card border border-border rounded-xl p-5">
                            <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center mb-2">
                                <i data-lucide="file-text" class="w-4 h-4 text-yellow-600"></i>
                            </div>
                            <p class="text-2xl font-bold text-foreground">{{ $totalApplications }}</p>
                            <p class="text-xs text-muted mt-1">Applications</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                        {{-- Users by Role --}}
                        <section class="bg-card border border-border rounded-xl p-6">
                            <h2 class="text-sm font-semibold text-foreground mb-4">Users by Role</h2>
                            <div class="space-y-3">
                                @foreach($usersByRole as $role => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-foreground capitalize">{{ $role }}</span>
                                        <span class="text-sm font-semibold text-foreground">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                        {{-- Listings by Status --}}
                        <section class="bg-card border border-border rounded-xl p-6">
                            <h2 class="text-sm font-semibold text-foreground mb-4">Jobs by Status</h2>
                            <div class="space-y-3">
                                @foreach($listingsByStatus as $status => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-foreground capitalize">{{ $status }}</span>
                                        <span class="text-sm font-semibold text-foreground">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                        {{-- Applications by Status --}}
                        <section class="bg-card border border-border rounded-xl p-6">
                            <h2 class="text-sm font-semibold text-foreground mb-4">Applications by Status</h2>
                            <div class="space-y-3">
                                @foreach($applicationsByStatus as $status => $count)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-foreground capitalize">{{ $status }}</span>
                                        <span class="text-sm font-semibold text-foreground">{{ $count }}</span>
                                    </div>
                                @endforeach 
                            </div>
                        </section>
                    </div> 
                </div>
            </div>
        </div>
    </div>
@endsection 