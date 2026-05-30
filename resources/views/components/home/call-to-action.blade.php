@php
    $isGuest = !auth()->check();
@endphp

<section aria-labelledby="cta-heading" class="bg-gradient-to-r from-primary to-primary/80 rounded-xl px-6 py-16 md:px-12 md:py-20 text-center">
    <h2 id="cta-heading" class="text-3xl md:text-4xl font-bold text-white mb-4">
        Ready to find your next opportunity?
    </h2>

    <p class="text-white/90 text-lg max-w-2xl mx-auto mb-8">
        Join thousands of professionals who have found their dream jobs through our platform. Start your journey today.
    </p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        @if($isGuest)
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-primary font-semibold px-8 py-3 rounded-lg hover:bg-white/90 transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-white">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Sign Up Free
            </a>
            <a href="{{ route('employer.register') }}" class="inline-flex items-center gap-2 border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white/10 transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-white">
                <i data-lucide="building-2" class="w-5 h-5"></i>
                Post a Job
            </a>
        @else
            <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-2 bg-white text-primary font-semibold px-8 py-3 rounded-lg hover:bg-white/90 transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-white">
                <i data-lucide="search" class="w-5 h-5"></i>
                Browse Jobs
            </a>
            <a href="{{ route('companies.index') }}" class="inline-flex items-center gap-2 border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white/10 transition-colors focus:outline-2 focus:outline-offset-2 focus:outline-white">
                <i data-lucide="building-2" class="w-5 h-5"></i>
                Explore Companies
            </a>
        @endif
    </div>
</section>
