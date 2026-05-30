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

        <form method="POST" action="/employer/register" class="space-y-5">
            @csrf

            <p class="text-xs font-semibold uppercase tracking-wider text-muted">About you</p>

            <div>
                <label for="name" class="text-sm text-foreground">Your name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Jane Smith">
            </div>

            <div>
                <label for="email" class="text-sm text-foreground">Work email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="you@company.com">
            </div>

            <div>
                <label for="password" class="text-sm text-foreground">Password</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Min 8 characters">
            </div>

            <div class="pt-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Your company</p>
            </div>

            <div>
                <label for="company_name" class="text-sm text-foreground">Company name</label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Acme Inc.">
            </div>

            <div>
                <label for="industry" class="text-sm text-foreground">Industry</label>
                <input type="text" id="industry" name="industry" value="{{ old('industry') }}" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="Technology, Healthcare, Finance...">
            </div>

            <div>
                <label for="description" class="text-sm text-foreground">What does your company do?</label>
                <textarea id="description" name="description" rows="3" required class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm resize-none" placeholder="A brief description...">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light transition-colors mt-2">
                Create employer account
            </button>
        </form>

        <p class="mt-8 text-sm text-muted">Already registered? <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Sign in</a></p>
        <p class="mt-2 text-xs text-muted">Looking for a job? <a href="{{ route('register') }}" class="text-primary hover:underline">Sign up as job seeker</a></p>
    </div>
@endsection
