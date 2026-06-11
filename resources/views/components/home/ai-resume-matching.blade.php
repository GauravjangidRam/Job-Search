@props(['aiFeatures'])

<section aria-labelledby="ai-resume-heading">
    {{-- Section Header --}}
    <div class="text-center mb-12">
        <h2 id="ai-resume-heading" class="text-2xl md:text-3xl font-bold text-foreground mb-3">
            AI-Powered Resume Matching
        </h2>
        <p class="text-muted max-w-2xl mx-auto">
            Our intelligent matching system analyzes your resume and connects you with the most relevant opportunities based on your skills and experience.
        </p>
    </div>

    {{-- Two-Column Layout --}}
    <div class="md:grid md:grid-cols-2 gap-12">
        {{-- Left Column: Features List --}}
        <div class="mb-8 md:mb-0">
            <div class="space-y-6">
                @foreach ($aiFeatures as $feature)
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-accent rounded-lg flex items-center justify-center">
                            <i data-lucide="{{ $feature->icon }}" class="w-5 h-5 text-primary"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-foreground mb-1">{{ $feature->title }}</h3>
                            <p class="text-sm text-muted">{{ Str::limit($feature->description, 120) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Right Column: Resume Analysis Card --}}
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-foreground mb-6">Resume Analysis</h3>

            @auth
                <div class="space-y-5">
                    {{-- Technical Skills --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-foreground">Technical Skills</span>
                            <span class="text-sm font-semibold text-primary">92%</span>
                        </div>
                        <div class="w-full h-2 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100" aria-label="Technical Skills: 92%">
                            <div class="h-full bg-primary rounded-full" style="width: 92%"></div>
                        </div>
                    </div>

                    {{-- Experience Match --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-foreground">Experience Match</span>
                            <span class="text-sm font-semibold text-primary">78%</span>
                        </div>
                        <div class="w-full h-2 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100" aria-label="Experience Match: 78%">
                            <div class="h-full bg-primary rounded-full" style="width: 78%"></div>
                        </div>
                    </div>

                    {{-- Education --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-foreground">Education</span>
                            <span class="text-sm font-semibold text-primary">85%</span>
                        </div>
                        <div class="w-full h-2 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100" aria-label="Education: 85%">
                            <div class="h-full bg-primary rounded-full" style="width: 85%"></div>
                        </div>
                    </div>

                    {{-- Soft Skills --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-foreground">Soft Skills</span>
                            <span class="text-sm font-semibold text-primary">88%</span>
                        </div>
                        <div class="w-full h-2 bg-secondary rounded-full overflow-hidden" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100" aria-label="Soft Skills: 88%">
                            <div class="h-full bg-primary rounded-full" style="width: 88%"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <form method="POST" action="{{ route('resume.analyze') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-center gap-3">
                            <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="block w-full text-sm text-muted file:py-2 file:px-4 file:rounded-md file:border file:border-border file:bg-background file:text-foreground" />
                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md">Analyze</button>
                        </div>
                    </form>
                </div>
            @else
                <div class="p-6 text-center">
                    <p class="text-muted mb-4">Please <a href="{{ route('login') }}" class="text-primary underline">login</a> to use resume analysis.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('login') }}" class="px-4 py-2 bg-primary text-white rounded-md">Login</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-secondary text-foreground rounded-md">Register</a>
                    </div>
                </div>
            @endauth

        </div>
    </div>
</section>
