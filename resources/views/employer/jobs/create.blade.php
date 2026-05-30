@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1300px] mx-auto pt-16">
        <div class="py-10 px-6 md:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Sidebar --}}
                <x-employer.sidebar />

                {{-- Main Content --}}
                <div class="flex-1 min-w-0">
            <div class="mb-8">
                <a href="{{ route('employer.jobs.index') }}" class="text-sm text-primary hover:underline">&larr; Back to job listings</a>
                <h1 class="text-2xl md:text-3xl font-bold text-foreground mt-3 mb-2">Create Job Listing</h1>
                <p class="text-muted">Fill in the details below to post a new job. New listings start as a draft.</p>
            </div>

            {{-- Validation Error Summary --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800" role="alert">
                    <p class="font-medium mb-2">Please correct the following errors:</p>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('employer.jobs.store') }}" class="p-6 md:p-8">
                    @csrf

                    {{-- Title --}}
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium mb-1">Job Title <span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            required
                            maxlength="255"
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('title') border-red-500 @enderror"
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium mb-1">Description <span class="text-red-600">*</span></label>
                        <textarea
                            id="description"
                            name="description"
                            rows="8"
                            required
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('description') border-red-500 @enderror"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Location --}}
                    <div class="mb-6">
                        <label for="location" class="block text-sm font-medium mb-1">Location <span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="{{ old('location') }}"
                            required
                            maxlength="255"
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('location') border-red-500 @enderror"
                        >
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Salary Range --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="salary_min" class="block text-sm font-medium mb-1">Minimum Salary <span class="text-red-600">*</span></label>
                            <input
                                type="number"
                                id="salary_min"
                                name="salary_min"
                                value="{{ old('salary_min') }}"
                                required
                                min="0"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('salary_min') border-red-500 @enderror"
                            >
                            @error('salary_min')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="salary_max" class="block text-sm font-medium mb-1">Maximum Salary <span class="text-red-600">*</span></label>
                            <input
                                type="number"
                                id="salary_max"
                                name="salary_max"
                                value="{{ old('salary_max') }}"
                                required
                                min="0"
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('salary_max') border-red-500 @enderror"
                            >
                            @error('salary_max')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Job Type & Location Type --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="job_type" class="block text-sm font-medium mb-1">Job Type <span class="text-red-600">*</span></label>
                            <select
                                id="job_type"
                                name="job_type"
                                required
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('job_type') border-red-500 @enderror"
                            >
                                <option value="">Select a job type</option>
                                @foreach(['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'] as $type)
                                    <option value="{{ $type }}" @selected(old('job_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('job_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="location_type" class="block text-sm font-medium mb-1">Location Type <span class="text-red-600">*</span></label>
                            <select
                                id="location_type"
                                name="location_type"
                                required
                                class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('location_type') border-red-500 @enderror"
                            >
                                <option value="">Select a location type</option>
                                @foreach(['Remote', 'On-site', 'Hybrid'] as $type)
                                    <option value="{{ $type }}" @selected(old('location_type') === $type)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('location_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Skills (Alpine repeater) --}}
                    <div class="mb-6" x-data="{
                        skills: {{ Js::from(old('skills', [''])) }},
                        addSkill() { this.skills.push(''); },
                        removeSkill(index) {
                            this.skills.splice(index, 1);
                            if (this.skills.length === 0) { this.skills.push(''); }
                        }
                    }">
                        <label class="block text-sm font-medium mb-1">Skills</label>
                        <p class="text-xs text-muted mb-2">Add the skills required for this role. Leave blank if none.</p>
                        <div class="space-y-2">
                            <template x-for="(skill, index) in skills" :key="index">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        name="skills[]"
                                        x-model="skills[index]"
                                        maxlength="255"
                                        placeholder="e.g. Laravel"
                                        class="flex-1 px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                    >
                                    <button
                                        type="button"
                                        @click="removeSkill(index)"
                                        class="shrink-0 px-3 py-2 border border-border text-red-600 rounded-md hover:bg-red-50 focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                                        aria-label="Remove skill"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button
                            type="button"
                            @click="addSkill()"
                            class="mt-3 inline-flex items-center px-4 py-2 border border-border text-foreground text-sm font-medium rounded-md hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            + Add Skill
                        </button>
                        @error('skills')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('skills.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3">
                        <a
                            href="{{ route('employer.jobs.index') }}"
                            class="px-6 py-3 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Create Job
                        </button>
                    </div>
                </form>
            </div>
                </div>
            </div>
        </div>
    </div>
@endsection
