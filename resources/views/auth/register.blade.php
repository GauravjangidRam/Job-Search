@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />
    <div class="max-w-[420px] mx-auto pt-28 pb-16 px-5">
        <h1 class="text-3xl font-bold text-foreground">Create account</h1>
        <p class="text-muted mt-2 mb-8">Join thousands of professionals finding their next role.</p>
        @if($errors->any())
            <div class="mb-4">
                @foreach($errors->all() as $error)
                    <p class="text-red-600 text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif
        <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-3 w-full py-3 border border-border rounded-lg text-sm font-medium text-foreground hover:bg-secondary transition-colors">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Continue with Google
        </a> 
        <div class="flex items-center gap-4 my-6">
            <div class="flex-1 h-px bg-border"></div>
            <span class="text-xs text-muted">or</span>
            <div class="flex-1 h-px bg-border"></div>
        </div>
        <form method="POST" action="/register" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <div> 
                <label for="name" class="text-sm text-foreground">Full name</label>
                <input type="text" name="name" value="{{ old('name') }}" 
                required autocomplete="name"
                :class="loading ? 'opacity-50 pointer-events-none' : ''"
                class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" 
                placeholder="Gaurav jangid">
            </div>
            <div>
                <label for="email" class="text-sm text-foreground">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" placeholder="name@company.com">
            </div>
            <div>
                <label for="password" class="text-sm text-foreground">Password</label>
                <input type="password" name="password" required autocomplete="new-password"
                :class="loading ? 'opacity-50 pointer-events-none' : ''"
                class="mt-1 block w-full border-0 border-b-2 border-border bg-transparent py-2 text-foreground placeholder:text-muted focus:border-primary focus:ring-0 text-sm" 
                placeholder="Min 8 characters">
            </div>
            <button type="submit" :class="loading ? 'opacity-70 pointer-events-none cursor-not-allowed' : ''" class="w-full py-3 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light transition-colors mt-2 flex items-center justify-center gap-2">
                <svg x-show="loading" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="loading ? 'Creating account...' : 'Create account'">Create account</span>
            </button>
        </form>
        <p class="mt-8 text-sm text-muted">Already have an account? <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Sign in</a></p>
        <p class="mt-2 text-xs text-muted">Hiring? <a href="{{ route('employer.register') }}" class="text-primary hover:underline">Register as employer</a></p>
    </div>
@endsection