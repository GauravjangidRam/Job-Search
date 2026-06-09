@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />
    <div class="max-w-[480px] mx-auto pt-28 pb-16 px-5">
        <h1 class="text-3xl font-bold text-foreground">Start hiring</h1>
        <p class="text-muted mt-2 mb-8">Create your employer account and post jobs to find the right talent.</p>
        @if($errors->any())
            <div class="mb-4">
                @foreach($errors->all() as $error)
                    <p class="text-red-600 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <form method="POST" action="/employer/register" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">About you</p>
            <div>
                <label for="name" class="text-sm text-foreground">Your name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Jane Smith" :disabled="loading">
            </div>
            <div>
                <label for="email" class="text-sm text-foreground">Work email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="you@company.com" :disabled="loading">
            </div>
            <div>
                <label for="password" class="text-sm text-foreground">Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Min 8 characters" :disabled="loading">
            </div>
            <div class="pt-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Your company</p>
            </div>
            <div>
                <label for="company_name" class="text-sm text-foreground">Company name</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Acme Inc." :disabled="loading">
            </div>
            <div>
                <label for="industry" class="text-sm text-foreground">Industry</label>
                <input type="text" id="industry" name="industry" value="{{ old('industry') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Technology, Healthcare, Finance..." :disabled="loading">
            </div>
            <div>
                <label for="description" class="text-sm text-foreground">What does your company do?</label>
                <textarea id="description" name="description" rows="3" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm resize-none" placeholder="A brief description..." :disabled="loading">{{ old('description') }}</textarea>
            </div>
            <button type="submit" :disabled="loading" class="w-full py-3 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light transition-colors mt-2 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Creating account...' : 'Create employer account'">Create employer account</span>
            </button>
        </form>
        <p class="mt-8 text-sm text-muted">Already registered? <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Sign in</a></p>
        <p class="mt-2 text-xs text-muted">Looking for a job? <a href="{{ route('register') }}" class="text-primary hover:underline">Sign up as job seeker</a></p>
    </div>
@endsection