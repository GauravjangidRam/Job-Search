<footer class="border-t border-border mt-16" aria-label="Site footer">
    <div class="max-w-[1400px] mx-auto px-6 md:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-10">
            {{-- For Job Seekers --}}
            <div>
                <h3 class="text-sm font-semibold text-foreground mb-4">For Job Seekers</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('jobs.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Browse Jobs</a></li>
                    <li><a href="{{ route('companies.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Companies</a></li>
                    <li><a href="{{ route('insights.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Career Insights</a></li>
                    <li><a href="{{ route('resume.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Resume Tips</a></li>
                </ul>
            </div>
            {{-- For Employers --}}
            <div>
                <h3 class="text-sm font-semibold text-foreground mb-4">For Employers</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('employer.register') }}" class="text-sm text-muted hover:text-primary transition-colors">Post a Job</a></li>
                    <li><a href="{{ route('companies.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Company Profiles</a></li>
                    <li><a href="{{ route('login') }}" class="text-sm text-muted hover:text-primary transition-colors">Employer Login</a></li>
                </ul>
            </div>
            {{-- Resources --}} 
            <div>
                <h3 class="text-sm font-semibold text-foreground mb-4">Resources</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('resume.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Resume Guide</a></li>
                    <li><a href="{{ route('insights.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Salary Data</a></li>
                    <li><a href="{{ route('insights.index') }}" class="text-sm text-muted hover:text-primary transition-colors">Hiring Trends</a></li>
                </ul>
            </div>
            {{-- Connect --}}
            <div>
                <h3 class="text-sm font-semibold text-foreground mb-4">Connect</h3>
                <div class="flex items-center gap-3 mb-4">
                    <a href="{{ config('services.social.twitter') }}" target="_blank" rel="noopener noreferrer" aria-label="Twitter" class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center text-muted hover:text-primary hover:bg-primary/10 transition-colors">
                        <i data-lucide="twitter" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ config('services.social.linkedin') }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center text-muted hover:text-primary hover:bg-primary/10 transition-colors">
                        <i data-lucide="linkedin" class="w-4 h-4"></i>
                    </a> 
                    <a href="{{ config('services.social.github') }}" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="w-8 h-8 rounded-lg bg-secondary flex items-center justify-center text-muted hover:text-primary hover:bg-primary/10 transition-colors">
                        <i data-lucide="github" class="w-4 h-4"></i>
                    </a>
                </div>
                <p class="text-xs text-muted">Find your next opportunity with Job Hub.</p>
            </div>
        </div>
        {{-- Bottom Bar --}}
        <div class="pt-8 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-lg font-bold text-primary">Job Hub</a>
            <p class="text-xs text-muted">&copy; {{ date('Y') }} Job Hub. All rights reserved.</p>
        </div>
    </div>
</footer> 