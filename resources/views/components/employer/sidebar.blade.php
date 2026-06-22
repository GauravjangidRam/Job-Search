@php
    $currentRoute = request()->route()?->getName() ?? '';
    $links = [
        ['route' => 'employer.dashboard', 'label' => 'Dashboard', 'icon' => 'bar-chart-2'],
        ['route' => 'employer.jobs.index', 'label' => 'Job Listings', 'icon' => 'briefcase'],
        ['route' => 'employer.applications.index', 'label' => 'Applications', 'icon' => 'file-text'],
        ['route' => 'employer.company.edit', 'label' => 'Company Profile', 'icon' => 'building-2'],
    ];
@endphp
<aside class="w-full lg:w-64 flex-shrink-0">
    <nav class="bg-card border border-border rounded-xl shadow-sm p-4 sticky top-24" aria-label="Employer navigation">
        <div class="mb-4 px-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted">Employer Panel</p>
        </div>
        <ul class="space-y-1">
            @foreach($links as $link)
                @php
                    $isActive = str_starts_with($currentRoute, $link['route']) ||
                        ($link['route'] === 'employer.jobs.index' && str_starts_with($currentRoute, 'employer.jobs'));
                @endphp
                <li>
                    <a
                        href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                            {{ $isActive
                                ? 'bg-primary/10 text-primary'
                                : 'text-foreground hover:bg-secondary hover:text-primary' }}"
                        @if($isActive) aria-current="page" @endif
                    >
                        <i data-lucide="{{ $link['icon'] }}" class="w-4 h-4"></i>
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</aside>
