@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1300px] mx-auto pt-16">
        <div class="py-10 px-6 md:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <x-employer.sidebar />

                <div class="flex-1 min-w-0">
                    <div class="bg-card border border-border rounded-xl p-10 text-center max-w-lg mx-auto">
                        @if($company->verification_status === 'rejected')
                            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="x-circle" class="w-7 h-7 text-red-600"></i>
                            </div>
                            <h2 class="text-xl font-bold text-foreground mb-2">Company Rejected</h2>
                            <p class="text-muted text-sm mb-4">Your company "{{ $company->name }}" was not approved by our admin team.</p>
                            @if($company->rejection_reason)
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                                    <p class="text-sm text-red-800"><span class="font-medium">Reason:</span> {{ $company->rejection_reason }}</p>
                                </div>
                            @endif
                            <p class="text-xs text-muted">Please update your company profile and contact support for re-review.</p>
                        @else
                            <div class="w-14 h-14 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="clock" class="w-7 h-7 text-yellow-600"></i>
                            </div>
                            <h2 class="text-xl font-bold text-foreground mb-2">Pending Verification</h2>
                            <p class="text-muted text-sm mb-4">Your company "{{ $company->name }}" is being reviewed by our admin team. You'll be able to post jobs once approved.</p>
                            <p class="text-xs text-muted">This usually takes 1-2 business days. Make sure your company profile is complete to speed up the process.</p>
                        @endif

                        <div class="mt-6">
                            <a href="{{ route('employer.company.edit') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-light transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                Update Company Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
