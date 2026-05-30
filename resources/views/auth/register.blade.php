@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-16 pt-24">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-primary">Job Hub</a>
                <h1 class="text-xl font-bold text-foreground mt-4">Create your account</h1>
                <p class="text-muted text-sm mt-1">Start your job search journey today</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-sm p-6 md:p-8">
                {{-- Google Sign Up --}}
                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 px-4 py-2.5 border border-border rounded-lg bg-background text-foreground text-sm font-medium hover:bg-secondary transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-border"></div></div>
                    <div class="relative flex justify-center text-xs"><span class="bg-card px-3 text-muted">or</span></div>
                </div>

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" role="alert">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="/register">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-foreground mb-1.5">Full Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            placeholder="John Doe"
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('name') border-red-500 @enderror"
                        >
                    </div>

                    {{-- Email --}}
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-foreground mb-1.5">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('email') border-red-500 @enderror"
                        >
                    </div>

                    {{-- Password --}}
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-foreground mb-1.5">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Min 8 characters"
                            class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary @error('password') border-red-500 @enderror"
                        >
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
                    >
                        Create Account
                    </button>
                </form>
            </div>

            {{-- Footer Links --}}
            <div class="mt-6 text-center space-y-3">
                <p class="text-sm text-muted">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Sign in</a>
                </p>
                <p class="text-xs text-muted">
                    Want to hire talent?
                    <a href="{{ route('employer.register') }}" class="text-primary hover:underline font-medium">Register as Employer</a>
                </p>
            </div>
        </div>
    </div>
@endsection
