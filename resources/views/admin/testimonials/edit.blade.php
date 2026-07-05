@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div> 
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Edit Testimonial</h1>
                    <p class="text-muted">Update the testimonial details.</p>
                </div> 
                <a
                    href="{{ route('admin.testimonials.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                >
                    Back to Testimonials
                </a>
            </div>

            {{-- Form --}}
            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-6 md:p-8">
                <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-foreground mb-1">Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $testimonial->name) }}"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                            placeholder="Enter person's name"
                        />
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role --}}
                    <div>
                        <label for="role" class="block text-sm font-medium text-foreground mb-1">Role</label>
                        <input
                            type="text"
                            id="role"
                            name="role"
                            value="{{ old('role', $testimonial->role) }}"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                            placeholder="e.g. Software Engineer"
                        />
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Company --}}
                    <div>
                        <label for="company" class="block text-sm font-medium text-foreground mb-1">Company</label>
                        <input
                            type="text"
                            id="company"
                            name="company"
                            value="{{ old('company', $testimonial->company) }}"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                            placeholder="e.g. Google"
                        />
                        @error('company')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Avatar URL --}}
                    <div>
                        <label for="avatar_url" class="block text-sm font-medium text-foreground mb-1">Avatar URL</label>
                        <input
                            type="url"
                            id="avatar_url"
                            name="avatar_url"
                            value="{{ old('avatar_url', $testimonial->avatar_url) }}"
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                            placeholder="https://example.com/avatar.jpg"
                        />
                        @error('avatar_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Text --}}
                    <div>
                        <label for="text" class="block text-sm font-medium text-foreground mb-1">Testimonial Text</label>
                        <textarea
                            id="text"
                            name="text"
                            rows="4"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors resize-y"
                            placeholder="Enter the testimonial content..."
                        >{{ old('text', $testimonial->text) }}</textarea>
                        @error('text')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rating --}}
                    <div>
                        <label for="rating" class="block text-sm font-medium text-foreground mb-1">Rating</label>
                        <input
                            type="number"
                            id="rating"
                            name="rating"
                            value="{{ old('rating', $testimonial->rating) }}"
                            min="1"
                            max="5"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                        />
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Is Featured --}}
                    <div class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            id="is_featured"
                            name="is_featured"
                            value="1"
                            {{ old('is_featured', $testimonial->is_featured) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-border text-primary focus:ring-primary"
                        />
                        <label for="is_featured" class="text-sm font-medium text-foreground">Featured testimonial</label>
                        @error('is_featured')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-4 pt-4">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Update Testimonial
                        </button>
                        <a
                            href="{{ route('admin.testimonials.index') }}"
                            class="inline-flex items-center justify-center px-6 py-2.5 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
