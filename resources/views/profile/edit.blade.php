@extends('layouts.app')

@section('content')
    <x-home.navigation-bar /> 

    <div class="max-w-[800px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            <h1 class="text-2xl md:text-3xl font-bold text-foreground mb-2">Edit Profile</h1>
            <p class="text-muted mb-8">Update your personal details and avatar.</p>
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

            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                <form
                    method="POST"
                    action="{{ route('profile.update') }}"
                    enctype="multipart/form-data"
                    class="p-6 md:p-8"
                >
                    @csrf
                    @method('PUT')
                    {{-- Name --}}
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            maxlength="255"
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('name') border-red-500 @enderror"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email (disabled / not editable) --}}
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium mb-1">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ $user->email }}"
                            disabled
                            readonly
                            class="w-full px-4 py-2 border border-border rounded-md bg-secondary text-muted cursor-not-allowed"
                        >
                        <p class="mt-1 text-xs text-muted">Your email address cannot be changed.</p>
                    </div>

                    {{-- Phone --}}
                    <div class="mb-6">
                        <label for="phone" class="block text-sm font-medium mb-1">Phone</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            maxlength="20"
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('phone') border-red-500 @enderror"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Bio --}}
                    <div class="mb-6">
                        <label for="bio" class="block text-sm font-medium mb-1">Bio</label>
                        <textarea
                            id="bio"
                            name="bio"
                            rows="5"
                            maxlength="5000"
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('bio') border-red-500 @enderror"
                        >{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Current Avatar Preview --}}
                    @php
                        $currentAvatar = $user->avatar_url ?? 
                                        (!empty($user->avatar_path) ? asset('storage/' . ltrim($user->avatar_path, '/')) : null);
                    @endphp
                    @if($currentAvatar)
                        <div class="mb-4">
                            <p class="block text-sm font-medium mb-2">Current Avatar</p>
                            <img
                                src="{{ $currentAvatar }}"
                                alt="{{ $user->name }}'s avatar"
                                class="h-16 w-16 object-cover rounded-full border border-border bg-background"
                            >
                        </div>
                    @endif

                    {{-- Avatar Upload --}}
                    <div class="mb-6">
                        <label for="avatar" class="block text-sm font-medium mb-1">Avatar</label>
                        <input
                            type="file"
                            id="avatar"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full px-4 py-2 border border-border rounded-md bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('avatar') border-red-500 @enderror"
                        >
                        <p class="mt-1 text-xs text-muted">Upload a JPEG, PNG, or WebP image up to 2 MB. Uploading a new file replaces the current avatar.</p>
                        @error('avatar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3">
                        <a
                            href="{{ route('profile.show') }}"
                            class="px-6 py-3 border border-border text-foreground font-semibold rounded-lg hover:bg-secondary focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Cancel
                        </a>
                        <button
                            type="submit"
                            class="px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-light focus:outline-2 focus:outline-offset-2 focus:outline-primary transition-colors"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
