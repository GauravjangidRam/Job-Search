@php
    $currentRoute = request()->route()?->getName() ?? '';
    $pendingCompanies = \App\Models\Company::where('verification_status', 'pending')->count();
    $links = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bar-chart-2', 'badge' => null],
        ['route' => 'admin.companies.index', 'label' => 'Companies', 'icon' => 'building-2', 'badge' => $pendingCompanies > 0 ? $pendingCompanies : null],
        ['route' => 'admin.jobs.index', 'label' => 'Job Listings', 'icon' => 'briefcase', 'badge' => null],
        ['route' => 'admin.applications.index', 'label' => 'Applications', 'icon' => 'file-text', 'badge' => null],
        ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'users', 'badge' => null],
        ['route' => 'admin.testimonials.index', 'label' => 'Testimonials', 'icon' => 'message-square', 'badge' => null],
        ['route' => 'admin.insights.index', 'label' => 'Career Insights', 'icon' => 'trending-up', 'badge' => null],
    ];
@endphp

<aside class="w-full lg:w-64 flex-shrink-0">
    <nav class="bg-card border border-border rounded-xl shadow-sm p-4 sticky top-24" aria-label="Admin navigation">
        <div class="mb-4 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Admin Panel</p>
        </div>
        <ul class="space-y-1">
            @foreach($links as $link)
                @php
                    $isActive = str_starts_with($currentRoute, $link['route']);
                @endphp
                <li> 
                    <a
                        href="{{ route($link['route']) }}"
                        class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                            {{ $isActive
                                ? 'bg-primary/10 text-primary'
                                : 'text-foreground hover:bg-secondary hover:text-primary' }}"
                        @if($isActive) aria-current="page" @endif
                    >
                        <span class="flex items-center gap-3">
                            <i data-lucide="{{ $link['icon'] }}" class="w-4 h-4"></i>
                            {{ $link['label'] }}
                        </span>
                        @if($link['badge'])
                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold bg-red-500 text-white rounded-full">{{ $link['badge'] }}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul> 
    </nav>
</aside>