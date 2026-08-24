@props(['aiFeatures'])

<section aria-labelledby="ai-resume-heading">
    {{-- Section Header --}}
    <div class="text-center max-w-2xl mx-auto mb-12">
        <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary text-xs font-semibold rounded-full mb-3">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            <span>Smart Career Assistant</span>
        </div>
        <h2 id="ai-resume-heading" class="text-2xl md:text-3xl font-extrabold text-foreground tracking-tight mb-3">
            AI-Powered Resume Matching
        </h2>
        <p class="text-muted text-sm md:text-base leading-relaxed">
            Our intelligent matching system analyzes your resume and connects you with the most relevant opportunities based on your skills and experience.
        </p>
    </div>
    {{-- Two-Column Layout --}}
    <div class="md:grid md:grid-cols-2 gap-8 items-center">
        {{-- Left Column: Features List --}}
        <div class="mb-8 md:mb-0 space-y-6">
            @foreach ($aiFeatures as $feature)
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-card border border-border/80 shadow-xs hover:border-primary/40 transition-all">
                    <div class="flex-shrink-0 w-11 h-11 bg-primary/10 rounded-xl flex items-center justify-center">
                        <i data-lucide="{{ $feature->icon }}" class="w-5 h-5 text-primary"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-foreground mb-1">{{ $feature->title }}</h3>
                        <p class="text-xs md:text-sm text-muted leading-relaxed">{{ Str::limit($feature->description, 120) }}</p>
                    </div>
                </div>
            @endforeach
        </div> 

        {{-- Right Column: Resume Analysis Card --}}
        <div class="bg-card border border-border/80 rounded-2xl p-6 md:p-8 shadow-md">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-foreground">Resume Analysis</h3>
                <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-600 rounded-full border border-emerald-500/20">Live Preview</span>
            </div>

            @auth
                <div class="space-y-5">
                    {{-- Technical Skills --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-foreground">Technical Skills</span>
                            <span class="text-xs font-bold text-primary">92%</span>
                        </div>
                        <div class="w-full h-2.5 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100" aria-label="Technical Skills: 92%">
                            <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: 92%"></div>
                        </div>
                    </div>

                    {{-- Experience Match --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-foreground">Experience Match</span>
                            <span class="text-xs font-bold text-primary">78%</span>
                        </div>
                        <div class="w-full h-2.5 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100" aria-label="Experience Match: 78%">
                            <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: 78%"></div>
                        </div>
                    </div>

                    {{-- Education --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-foreground">Education</span>
                            <span class="text-xs font-bold text-primary">85%</span>
                        </div>
                        <div class="w-full h-2.5 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100" aria-label="Education: 85%">
                            <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: 85%"></div>
                        </div>
                    </div>

                    {{-- Soft Skills --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-foreground">Soft Skills</span>
                            <span class="text-xs font-bold text-primary">88%</span>
                        </div>
                        <div class="w-full h-2.5 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100" aria-label="Soft Skills: 88%">
                            <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: 88%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-border/70">
                    <form method="POST" action="{{ route('resume.analyze') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-center gap-3">
                            <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="block w-full text-xs text-muted file:py-2 file:px-3 file:rounded-lg file:border file:border-border file:bg-background file:text-foreground file:font-semibold" />
                            <button type="submit" class="px-4 py-2 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-primary-light transition-all shadow-xs">Analyze</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-6 text-center">
                    <p class="text-xs md:text-sm text-muted mb-4">Please <a href="{{ route('login') }}" class="text-primary font-semibold underline">login</a> to analyze your resume.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-primary-light transition-all shadow-xs">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-secondary text-foreground text-xs font-semibold rounded-lg hover:bg-border/60 transition-all">Register</a>
                    </div>
                </div>
            @endauth

        </div>
    </div>
</section>
