@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-16 pt-24">
        <div class="w-full max-w-lg">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-primary">Job Hub</a>
                <h1 class="text-xl font-bold text-foreground mt-4">Register as Employer</h1>
                <p class="text-muted text-sm mt-1">Create your company account and start hiring</p>
            </div>

            <div class="bg-card border border-border rounded-xl shadow-sm p-6 md:p-8">
                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" role="alert">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="/employer/register">
                    @csrf

                    {{-- Personal Info Section --}}
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-foreground mb-3 pb-2 border-b border-border">Your Details</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-foreground mb-1.5">Full Name</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    placeholder="Your full name"
                                    class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                >
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-foreground mb-1.5">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="you@company.com"
                                    class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                >
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-foreground mb-1.5">Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    placeholder="Min 8 characters"
                                    class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                >
                            </div>
                        </div>
                    </div>

                    {{-- Company Info Section --}}
                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-foreground mb-3 pb-2 border-b border-border">Company Details</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="company_name" class="block text-sm font-medium text-foreground mb-1.5">Company Name</label>
                                <input
                                    type="text"
                                    id="company_name"
                                    name="company_name"
                                    value="{{ old('company_name') }}"
                                    required
                                    placeholder="Your company name"
                                    class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                >
                            </div>

                            <div>
                                <label for="industry" class="block text-sm font-medium text-foreground mb-1.5">Industry</label>
                                <input
                                    type="text"
                                    id="industry"
                                    name="industry"
                                    value="{{ old('industry') }}"
                                    required
                                    placeholder="e.g. Technology, Healthcare, Finance"
                                    class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                >
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-foreground mb-1.5">Company Description</label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="3"
                                    required
                                    placeholder="Brief description of what your company does..."
                                    class="w-full px-4 py-2.5 border border-border rounded-lg bg-background text-foreground placeholder:text-muted text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary resize-none"
                                >{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
                    >
                        Create Employer Account
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
                    Looking for a job instead?
                    <a href="{{ route('register') }}" class="text-primary hover:underline font-medium">Register as Job Seeker</a>
                </p>
            </div>
        </div>
    </div>
@endsection
