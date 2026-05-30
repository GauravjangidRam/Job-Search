@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Edit Career Insight</h1>
                    <p class="text-muted">Update the career insight details.</p>
                </div>
                <a
                    href="{{ route('admin.insights.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                >
                    Back to Insights
                </a>
            </div>

            {{-- Form --}}
            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm p-6 md:p-8">
                <form method="POST" action="{{ route('admin.insights.update', $insight) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Type --}}
                    <div>
                        <label for="type" class="block text-sm font-medium text-foreground mb-1">Type</label>
                        <select
                            id="type"
                            name="type"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                        >
                            <option value="salary" {{ old('type', $insight->type) === 'salary' ? 'selected' : '' }}>Salary</option>
                            <option value="trend" {{ old('type', $insight->type) === 'trend' ? 'selected' : '' }}>Trend</option>
                            <option value="skill" {{ old('type', $insight->type) === 'skill' ? 'selected' : '' }}>Skill</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Label --}}
                    <div>
                        <label for="label" class="block text-sm font-medium text-foreground mb-1">Label</label>
                        <input
                            type="text"
                            id="label"
                            name="label"
                            value="{{ old('label', $insight->label) }}"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                            placeholder="e.g. Average Software Engineer Salary"
                        />
                        @error('label')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Value --}}
                    <div>
                        <label for="value" class="block text-sm font-medium text-foreground mb-1">Value</label>
                        <input
                            type="text"
                            id="value"
                            name="value"
                            value="{{ old('value', $insight->value) }}"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                            placeholder="e.g. $120,000"
                        />
                        @error('value')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-foreground mb-1">Sort Order</label>
                        <input
                            type="number"
                            id="sort_order"
                            name="sort_order"
                            value="{{ old('sort_order', $insight->sort_order) }}"
                            min="0"
                            required
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted focus:outline-2 focus:outline-primary focus:border-primary transition-colors"
                        />
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-4 pt-4">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Update Insight
                        </button>
                        <a
                            href="{{ route('admin.insights.index') }}"
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
