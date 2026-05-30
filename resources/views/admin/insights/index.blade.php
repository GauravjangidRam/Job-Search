@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Career Insights</h1>
                    <p class="text-muted">Manage career insight entries displayed on the platform.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('admin.insights.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                    >
                        Create Insight
                    </a>
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                    >
                        Back to Dashboard
                    </a>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Insights Table --}}
            @if($insights->count() > 0)
                <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-secondary/50 border-b border-border">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Type</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Label</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Value</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Sort Order</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @php
                                    $typeBadgeStyles = [
                                        'salary' => 'bg-green-100 text-green-800',
                                        'trend'  => 'bg-blue-100 text-blue-800',
                                        'skill'  => 'bg-purple-100 text-purple-800',
                                    ];
                                @endphp
                                @foreach($insights as $insight)
                                    <tr class="hover:bg-secondary/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize {{ $typeBadgeStyles[$insight->type] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $insight->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-foreground">{{ $insight->label }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $insight->value }}</td>
                                        <td class="px-6 py-4 text-sm text-foreground">{{ $insight->sort_order }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ route('admin.insights.edit', $insight) }}"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-primary text-white rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                                                >
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.insights.destroy', $insight) }}" onsubmit="return confirm('Are you sure you want to delete this career insight?')">
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
                        Page {{ $insights->currentPage() }} of {{ $insights->lastPage() }}
                        &middot; {{ $insights->total() }} {{ Str::plural('insight', $insights->total()) }}
                    </p>
                    <div>
                        {{ $insights->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-card border border-border rounded-[var(--radius-card)] p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-foreground mb-2">No career insights yet</h2>
                    <p class="text-muted mb-4">Create your first career insight to display on the platform.</p>
                    <a
                        href="{{ route('admin.insights.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                    >
                        Create Insight
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
