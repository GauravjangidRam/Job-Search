@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">User Management</h1>
                    <p class="text-muted">View and manage all registered users.</p>
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

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 font-medium" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filter Bar --}}
            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-4 md:p-6 mb-6">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-foreground mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by name or email..."
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                        />
                    </div>
                    <div class="sm:w-48">
                        <label for="role" class="block text-sm font-medium text-foreground mb-1">Role</label>
                        <select
                            id="role"
                            name="role"
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                        >
                            <option value="">All Roles</option>
                            @foreach($roles as $roleOption)
                                <option value="{{ $roleOption }}" {{ $role === $roleOption ? 'selected' : '' }}>
                                    {{ ucfirst($roleOption) }}
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

            {{-- Users Table --}}
            @if($users->count() > 0)
                <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-secondary/50 border-b border-border">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Name</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Email</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Role</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Registered</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Change Role</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @php
                                    $roleBadgeStyles = [
                                        'seeker'   => 'bg-blue-100 text-blue-800',
                                        'employer' => 'bg-purple-100 text-purple-800',
                                        'admin'    => 'bg-orange-100 text-orange-800',
                                    ];
                                @endphp
                                @foreach($users as $user)
                                    <tr class="hover:bg-secondary/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-foreground">{{ $user->name }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $user->email }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $roleBadgeStyles[$user->role] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $user->created_at->format('M j, Y') }}</td>
                                        <td class="px-6 py-4">
                                            <form method="POST" action="{{ route('admin.users.updateRole', $user) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <select
                                                    name="role"
                                                    class="px-3 py-1.5 text-sm border border-border rounded-lg bg-background text-foreground focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                                                    aria-label="Change role for {{ $user->name }}"
                                                >
                                                    @foreach($roles as $roleOption)
                                                        <option value="{{ $roleOption }}" {{ $user->role === $roleOption ? 'selected' : '' }}>
                                                            {{ ucfirst($roleOption) }}
                                                        </option>
                                                    @endforeach
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
                        Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
                        &middot; {{ $users->total() }} {{ Str::plural('user', $users->total()) }}
                    </p>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-card border border-border rounded-[var(--radius-card)] p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-foreground mb-2">No users found</h2>
                    <p class="text-muted">Try adjusting your search or filter criteria.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
