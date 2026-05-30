@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Testimonials</h1>
                    <p class="text-muted">Manage testimonials displayed on the platform.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('admin.testimonials.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                    >
                        Create Testimonial
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

            {{-- Testimonials Table --}}
            @if($testimonials->count() > 0)
                <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-secondary/50 border-b border-border">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Name</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Role</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Company</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Rating</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Featured</th>
                                    <th class="px-6 py-3 text-xs font-semibold uppercase tracking-wide text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($testimonials as $testimonial)
                                    <tr class="hover:bg-secondary/30 transition-colors">
                                        <td class="px-6 py-4 font-medium text-foreground">{{ $testimonial->name }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $testimonial->role }}</td>
                                        <td class="px-6 py-4 text-sm text-muted">{{ $testimonial->company }}</td>
                                        <td class="px-6 py-4 text-sm text-foreground">{{ $testimonial->rating }}/5</td>
                                        <td class="px-6 py-4">
                                            @if($testimonial->is_featured)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Featured
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a
                                                    href="{{ route('admin.testimonials.edit', $testimonial) }}"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium bg-primary text-white rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                                                >
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" onsubmit="return confirm('Are you sure you want to delete this testimonial?')">
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
                        Page {{ $testimonials->currentPage() }} of {{ $testimonials->lastPage() }}
                        &middot; {{ $testimonials->total() }} {{ Str::plural('testimonial', $testimonials->total()) }}
                    </p>
                    <div>
                        {{ $testimonials->links() }}
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-card border border-border rounded-[var(--radius-card)] p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-foreground mb-2">No testimonials yet</h2>
                    <p class="text-muted mb-4">Create your first testimonial to display on the platform.</p>
                    <a
                        href="{{ route('admin.testimonials.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                    >
                        Create Testimonial
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
