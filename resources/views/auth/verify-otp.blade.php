@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-card rounded-[var(--radius-card)] border border-border shadow-sm p-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-foreground">Verify Your Email</h1>
                <p class="mt-2 text-sm text-muted">Enter the 6-digit code sent to your email</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                    <p class="text-sm text-green-700 dark:text-green-300">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ url('/verify-otp') }}">
                @csrf

                <div class="mb-4">
                    <label for="otp" class="block text-sm font-medium text-foreground mb-1">One-Time Password</label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        maxlength="6"
                        pattern="\d{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="000000"
                        class="w-full rounded-md border border-border bg-background px-4 py-2 text-center text-2xl tracking-widest text-foreground placeholder-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        required
                        autofocus
                    >
                    @error('otp')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors"
                >
                    Verify OTP
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-muted mb-2">Didn't receive the code?</p>
                <form method="POST" action="{{ url('/resend-otp') }}">
                    @csrf
                    <button
                        type="submit"
                        class="text-sm font-medium text-primary hover:text-primary-light underline focus:outline-none focus:ring-2 focus:ring-primary/20 rounded transition-colors"
                    >
                        Resend OTP
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
