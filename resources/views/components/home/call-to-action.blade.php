@php
    $isGuest = !auth()->check();
@endphp
<section aria-labelledby="cta-heading" class="bg-gradient-to-br from-primary via-primary/95 to-primary-light rounded-3xl px-6 py-16 md:px-12 md:py-20 text-center shadow-xl relative overflow-hidden">
    <div class="absolute -left-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
    <h2 id="cta-heading" class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-4">
        Ready to find your next opportunity?
    </h2>
    <p class="text-white/95 text-base md:text-lg max-w-2xl mx-auto mb-8 font-normal leading-relaxed">
        Join thousands of professionals who have found their dream jobs through our platform. Start your journey today.
    </p> 
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10">
        @if($isGuest)
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-primary font-bold px-8 py-3.5 rounded-xl hover:bg-white/90 shadow-md transition-all focus:outline-2 focus:outline-offset-2 focus:outline-white active:scale-95">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Sign Up Free
            </a>
            <a href="{{ route('employer.register') }}" class="inline-flex items-center gap-2 border-2 border-white/90 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-white/10 transition-all focus:outline-2 focus:outline-offset-2 focus:outline-white active:scale-95">
                <i data-lucide="building-2" class="w-5 h-5"></i>
                Post a Job
            </a> 
        @else
            <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-2 bg-white text-primary font-bold px-8 py-3.5 rounded-xl hover:bg-white/90 shadow-md transition-all focus:outline-2 focus:outline-offset-2 focus:outline-white active:scale-95">
                <i data-lucide="search" class="w-5 h-5"></i>
                Browse Jobs
            </a>
            <a href="{{ route('companies.index') }}" class="inline-flex items-center gap-2 border-2 border-white/90 text-white font-bold px-8 py-3.5 rounded-xl hover:bg-white/10 transition-all focus:outline-2 focus:outline-offset-2 focus:outline-white active:scale-95">
                <i data-lucide="building-2" class="w-5 h-5"></i>
                Explore Companies
            </a>
        @endif
    </div> 
</section>