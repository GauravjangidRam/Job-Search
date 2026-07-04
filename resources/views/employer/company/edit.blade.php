@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Sidebar --}}
                <x-employer.sidebar />
                {{-- Main Content --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Company Profile</h1>
                    <p class="text-muted mb-8">Manage how your company appears to job seekers.</p>
                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 font-medium" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

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

                    {{-- Preview Link --}}
                    @if($company->slug)
                        <div class="mb-6 p-4 bg-secondary/50 border border-border rounded-lg flex items-center justify-between">
                            <p class="text-sm text-muted">
                                <i data-lucide="eye" class="w-4 h-4 inline -mt-0.5 mr-1"></i>
                                Your public company page is live
                            </p>
                            <a href="{{ route('companies.show', $company->slug) }}" target="_blank" class="text-sm text-primary hover:underline font-medium">
                                View public page &rarr;
                            </a>
                        </div>
                    @endif

                    <div class="bg-card border border-border rounded-xl shadow-sm overflow-hidden">
                        <form
                            method="POST"
                            action="{{ route('employer.company.update') }}"
                            enctype="multipart/form-data"
                            class="p-6 md:p-8"
                        >
                            @csrf
                            @method('PUT')

                            {{-- Basic Information Section --}}
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold text-foreground mb-4 pb-2 border-b border-border">Basic Information</h2>

                                {{-- Company Name --}}
                                <div class="mb-5">
                                    <label for="name" class="block text-sm font-medium text-foreground mb-1">Company Name <span class="text-red-600">*</span></label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $company->name) }}"
                                        required
                                        maxlength="255"
                                        class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('name') border-red-500 @enderror"
                                    >
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Industry & Founded Year --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                    <div>
                                        <label for="industry" class="block text-sm font-medium text-foreground mb-1">Industry</label>
                                        <input
                                            type="text"
                                            id="industry"
                                            name="industry"
                                            value="{{ old('industry', $company->industry) }}"
                                            maxlength="100"
                                            placeholder="e.g. Technology, Healthcare"
                                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('industry') border-red-500 @enderror"
                                        >
                                        @error('industry')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="founded_year" class="block text-sm font-medium text-foreground mb-1">Founded Year</label>
                                        <input
                                            type="number"
                                            id="founded_year"
                                            name="founded_year"
                                            value="{{ old('founded_year', $company->founded_year) }}"
                                            min="1800"
                                            max="{{ date('Y') }}"
                                            placeholder="e.g. 2015"
                                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('founded_year') border-red-500 @enderror"
                                        >
                                        @error('founded_year')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Website & Employee Count --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                                    <div>
                                        <label for="website_url" class="block text-sm font-medium text-foreground mb-1">Website URL</label>
                                        <input
                                            type="url"
                                            id="website_url"
                                            name="website_url"
                                            value="{{ old('website_url', $company->website_url) }}"
                                            maxlength="2048"
                                            placeholder="https://example.com"
                                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('website_url') border-red-500 @enderror"
                                        >
                                        @error('website_url')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="employee_count" class="block text-sm font-medium text-foreground mb-1">Employee Count</label>
                                        <input
                                            type="number"
                                            id="employee_count"
                                            name="employee_count"
                                            value="{{ old('employee_count', $company->employee_count) }}"
                                            min="1"
                                            placeholder="e.g. 50"
                                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('employee_count') border-red-500 @enderror"
                                        >
                                        @error('employee_count')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Hiring Status --}}
                                <div class="mb-5">
                                    <label class="inline-flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="hidden"
                                            name="is_hiring"
                                            value="0"
                                        >
                                        <input
                                            type="checkbox"
                                            name="is_hiring"
                                            value="1"
                                            {{ old('is_hiring', $company->is_hiring) ? 'checked' : '' }}
                                            class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
                                        >
                                        <span class="text-sm font-medium text-foreground">We are currently hiring</span>
                                    </label>
                                    <p class="text-xs text-muted mt-1 ml-7">This shows a "Hiring" badge on your company profile.</p>
                                </div>
                            </div>

                            {{-- Description Section --}}
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold text-foreground mb-4 pb-2 border-b border-border">About Your Company</h2>

                                <div class="mb-5">
                                    <label for="description" class="block text-sm font-medium text-foreground mb-1">Description</label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="5"
                                        maxlength="5000"
                                        placeholder="Tell job seekers what your company does..."
                                        class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('description') border-red-500 @enderror"
                                    >{{ old('description', $company->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mb-5">
                                    <label for="culture" class="block text-sm font-medium text-foreground mb-1">Company Culture</label>
                                    <textarea
                                        id="culture"
                                        name="culture"
                                        rows="4"
                                        maxlength="5000"
                                        placeholder="Describe your work environment, values, and team culture..."
                                        class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('culture') border-red-500 @enderror"
                                    >{{ old('culture', $company->culture) }}</textarea>
                                    @error('culture')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Perks Section --}}
                            <div class="mb-8" x-data="{
                                perks: {{ Js::from(old('perks', $company->perks ?? [''])) }},
                                addPerk() { this.perks.push(''); },
                                removePerk(index) {
                                    this.perks.splice(index, 1);
                                    if (this.perks.length === 0) { this.perks.push(''); }
                                }
                            }">
                                <h2 class="text-lg font-semibold text-foreground mb-4 pb-2 border-b border-border">Perks & Benefits</h2>
                                <p class="text-xs text-muted mb-3">List the perks and benefits your company offers to employees.</p>

                                <div class="space-y-2">
                                    <template x-for="(perk, index) in perks" :key="index">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="text"
                                                name="perks[]"
                                                x-model="perks[index]"
                                                maxlength="255"
                                                placeholder="e.g. Remote work, Health insurance, Gym membership"
                                                class="flex-1 px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                            >
                                            <button
                                                type="button"
                                                @click="removePerk(index)"
                                                class="shrink-0 p-2 border border-border text-red-600 rounded-md hover:bg-red-50 focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                                                aria-label="Remove perk"
                                            >
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <button
                                    type="button"
                                    @click="addPerk()"
                                    class="mt-3 inline-flex items-center gap-1 px-4 py-2 border border-border text-foreground text-sm font-medium rounded-md hover:bg-secondary transition-colors"
                                >
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Add Perk
                                </button>
                            </div>

                            {{-- Logo Section --}}
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold text-foreground mb-4 pb-2 border-b border-border">Company Logo</h2>

                                @if(!empty($company->logo_url))
                                    <div class="mb-4 flex items-center gap-4">
                                        <img src="{{ $company->logo_url }}" alt="{{ $company->name }} logo" class="h-16 w-16 object-contain rounded-lg border border-border bg-background p-1">
                                        <p class="text-sm text-muted">Current logo</p>
                                    </div>
                                @endif

                                <div class="mb-5">
                                    <label for="logo" class="block text-sm font-medium text-foreground mb-1">Upload Logo</label>
                                    <input
                                        type="file"
                                        id="logo"
                                        name="logo"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('logo') border-red-500 @enderror"
                                    >
                                    <p class="mt-1 text-xs text-muted">JPEG, PNG, or WebP up to 2 MB.</p>
                                    @error('logo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="logo_url" class="block text-sm font-medium text-foreground mb-1">Or provide a Logo URL</label>
                                    <input
                                        type="url"
                                        id="logo_url"
                                        name="logo_url"
                                        value="{{ old('logo_url', $company->logo_url) }}"
                                        maxlength="2048"
                                        placeholder="https://example.com/logo.png"
                                        class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('logo_url') border-red-500 @enderror"
                                    >
                                    <p class="mt-1 text-xs text-muted">Used when no logo file is uploaded.</p>
                                    @error('logo_url')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                                <a href="{{ route('employer.dashboard') }}" class="px-5 py-2.5 border border-border text-foreground text-sm font-semibold rounded-lg hover:bg-secondary transition-colors">
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    class="px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
