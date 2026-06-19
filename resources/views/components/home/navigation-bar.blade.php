<nav x-data="mobileMenu" aria-label="Main navigation" class="fixed top-0 left-0 right-0 z-50 bg-card border-b border-border shadow-sm">
    <div class="max-w-[1400px] mx-auto px-6 md:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="/" class="text-xl font-bold text-primary focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded" aria-label="Job Hub - Home">Job Hub</a>
            </div>

            <!-- Desktop Menu Links (hidden below 768px) -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('jobs.index') }}" class="text-foreground hover:text-primary font-medium transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Jobs</a>
                <a href="{{ route('companies.index') }}" class="text-foreground hover:text-primary font-medium transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Companies</a>
                <a href="{{ route('insights.index') }}" class="text-foreground hover:text-primary font-medium transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Career Insights</a>
                <a href="{{ route('resume.index') }}" class="text-foreground hover:text-primary font-medium transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Resume</a>
            </div>

            <!-- Right Side Elements (hidden below 768px) -->
            <div class="hidden md:flex items-center space-x-4">
                <div x-data="topSearch" class="relative">
                    <button type="button" @click="toggle()" aria-label="Search" class="p-2 text-muted hover:text-primary rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>
                    <div x-show="open" x-cloak @click.away="close()" class="absolute right-0 mt-2 w-80 bg-card border border-border rounded-md shadow-lg p-2">
                        <div class="flex items-center gap-2">
                            <input x-ref="input" x-model="query" @keydown.enter.prevent="submit()" type="text" placeholder="Search jobs or companies" class="w-full px-3 py-2 border rounded bg-transparent focus:outline-none" />
                            <button type="button" @click="submit()" class="px-3 py-2 bg-primary text-white rounded">Go</button>
                        </div>
                    </div>
                </div>
                @auth
                    <a href="{{ route('notifications.index') }}" aria-label="Notifications" class="relative p-2 text-muted hover:text-primary rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="absolute top-1 right-1.5 block w-2 h-2 bg-red-500 rounded-full ring-2 ring-card"></span>
                        @endif
                    </a>
                @endauth

                @guest
                    <a href="{{ route('employer.register') }}" class="text-foreground hover:text-primary font-medium transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                        For Employers
                    </a>
                    <a href="/login" class="text-foreground hover:text-primary font-medium transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                        Login
                    </a>
                    <a href="/register" class="inline-flex items-center px-4 py-2 bg-primary text-white font-medium rounded-[var(--radius-card)] hover:bg-primary-light transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Register
                    </a>
                @endguest

                @auth
                    @if(auth()->user()->isEmployer())
                        <a href="{{ route('employer.dashboard') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary/10 text-primary text-sm font-medium rounded-lg hover:bg-primary/20 transition-colors">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                            Employer Panel
                        </a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary/10 text-primary text-sm font-medium rounded-lg hover:bg-primary/20 transition-colors">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            Admin
                        </a>
                    @else
                        <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-secondary text-foreground text-sm font-medium rounded-lg hover:bg-secondary/80 transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            Profile
                        </a>
                    @endif
                    <span class="text-foreground font-medium text-sm">
                        {{ Str::limit(auth()->user()->name, 15) }}
                    </span>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-muted hover:text-primary font-medium text-sm transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>

            <!-- Mobile Menu Toggle (visible below 768px) -->
            <button
                type="button"
                class="md:hidden p-2 text-muted hover:text-primary rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                @click="toggle()"
                :aria-expanded="open.toString()"
                aria-label="Toggle navigation menu"
            >
                <i x-show="!open" data-lucide="menu" class="w-6 h-6"></i>
                <i x-show="open" data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Panel -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        @keydown.escape.window="open = false"
        class="md:hidden border-t border-border bg-card"
        role="menu"
        x-cloak
    >
        <div class="px-6 py-4 space-y-3">
            <a href="{{ route('jobs.index') }}" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Jobs</a>
            <a href="{{ route('companies.index') }}" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Companies</a>
            <a href="{{ route('insights.index') }}" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Career Insights</a>
            <a href="{{ route('resume.index') }}" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">Resume</a>
            <div class="pt-3 border-t border-border">
                @guest 
                    <a href="{{ route('employer.register') }}" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                        For Employers
                    </a>
                    <a href="/login" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                        Login
                    </a>
                    <a href="/register" role="menuitem" class="inline-flex items-center px-4 py-2 bg-primary text-white font-medium rounded-[var(--radius-card)] hover:bg-primary-light transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        Register
                    </a>
                @endguest

                @auth
                    @if(auth()->user()->isEmployer())
                        <a href="{{ route('employer.dashboard') }}" role="menuitem" class="block text-primary font-medium py-2 transition-colors duration-200">
                            Employer Panel
                        </a>
                    @elseif(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" role="menuitem" class="block text-primary font-medium py-2 transition-colors duration-200">
                            Admin Panel
                        </a>
                    @else
                        <a href="{{ route('profile.show') }}" role="menuitem" class="block text-foreground hover:text-primary font-medium py-2 transition-colors duration-200">
                            My Profile
                        </a>
                    @endif
                    <span class="block text-foreground font-medium py-2">
                        {{ Str::limit(auth()->user()->name, 20) }}
                    </span>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" role="menuitem" class="text-muted hover:text-primary font-medium py-2 transition-colors duration-200 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                            Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav> 