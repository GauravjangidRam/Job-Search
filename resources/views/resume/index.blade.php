@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        {{-- Page Header --}}
        <section class="py-16 px-6 md:px-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 mb-5">
                <i data-lucide="file-text" class="w-8 h-8 text-primary"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-3">AI Resume Analysis</h1>
            <p class="text-muted max-w-xl mx-auto">Upload your resume to get a quick report with ATS checks, a readiness score, and improvement ideas.</p>
        </section>

        @if(session('success'))
            <div class="mx-6 md:mx-8 mb-8 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-6 md:mx-8 mb-8 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- AI Resume Upload --}}
        <section class="px-6 md:px-8 mb-12">
            <div class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.1fr] gap-6 items-start">
                <div class="bg-card border border-border rounded-xl p-6 md:p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10">
                            <i data-lucide="sparkles" class="w-5 h-5 text-primary"></i>
                        </span>
                        <div>
                            <h2 class="text-xl font-bold text-foreground">Upload Resume</h2>
                            <p class="text-sm text-muted">PDF, DOC, or DOCX up to 5 MB</p>
                        </div>
                    </div>

                    <div class="mb-5 inline-flex items-center gap-2 rounded-lg border border-border bg-secondary px-3 py-2 text-xs font-semibold text-muted">
                        <i data-lucide="brain-circuit" class="w-4 h-4 text-primary"></i>
                        Analysis mode: Local AI report
                    </div>

                    <form id="resume-analysis-form" action="{{ route('resume.analyze') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <label for="resume" class="block rounded-lg border border-dashed border-border bg-secondary/40 p-5 text-center cursor-pointer hover:border-primary/50 transition-colors">
                            <i data-lucide="upload-cloud" class="w-8 h-8 text-primary mx-auto mb-3"></i>
                            <span class="block text-sm font-semibold text-foreground">Choose your resume file</span>
                            <span class="block text-xs text-muted mt-1">The report will be saved to your account.</span>
                            <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx" required class="mt-4 block w-full text-sm text-muted file:mr-4 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-primary-light">
                        </label>

                        <button id="resume-analysis-button" type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-primary-light">
                            <i data-lucide="scan-search" class="w-4 h-4"></i>
                            Analyze Resume
                        </button>
                    </form>

                    <div id="resume-processing" class="hidden mt-6 rounded-lg border border-primary/20 bg-primary/5 p-5">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                <i data-lucide="loader-circle" class="w-5 h-5 text-primary animate-spin"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Analyzing your resume</p>
                                <p class="text-xs text-muted">Scanning format, ATS readability, and improvement signals.</p>
                            </div>
                        </div>
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-border">
                            <div class="h-full w-1/2 animate-pulse rounded-full bg-primary"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-card border border-border rounded-xl p-6 md:p-8">
                    @if($latestAnalysis)
                        @php
                            $report = $latestAnalysis->analysis ?? [];
                            $score = $report['score'] ?? 0;
                            $checks = $report['checks'] ?? [];
                            $suggestions = $report['suggestions'] ?? [];
                            $stats = [
                                ['label' => 'Words read', 'value' => $report['word_count'] ?? 0],
                                ['label' => 'Sections found', 'value' => count($report['detected_sections'] ?? [])],
                                ['label' => 'Keywords found', 'value' => $report['keyword_count'] ?? 0],
                            ];
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Latest report</p>
                                    <span class="rounded-md bg-secondary px-2 py-1 text-xs font-semibold text-muted">{{ isset($report['ai_ats_score']) ? 'AI Report' : 'Local Report' }}</span>
                                </div>
                                <h2 class="text-xl font-bold text-foreground mt-2">{{ $report['file_name'] ?? 'Uploaded resume' }}</h2>
                                <p class="text-sm text-muted mt-2">{{ $report['summary'] ?? 'Your resume report is ready.' }}</p>
                            </div>
                            <div class="shrink-0 rounded-lg border border-border bg-secondary px-5 py-4 text-center">
                                <p class="text-3xl font-bold text-primary">{{ $report['ai_ats_score'] ?? $score }}</p>
                                <p class="text-xs font-semibold text-muted">ATS Score</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mb-6">
                            @foreach($stats as $stat)
                                <div class="rounded-lg border border-border bg-secondary/40 p-3 text-center">
                                    <p class="text-lg font-bold text-foreground">{{ $stat['value'] }}</p>
                                    <p class="text-xs font-semibold text-muted">{{ $stat['label'] }}</p>
                                </div>
                            @endforeach
                        </div> 
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                            @foreach($checks as $check)
                                <div class="rounded-lg border border-border p-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="{{ ($check['status'] ?? '') === 'pass' ? 'check-circle-2' : 'alert-circle' }}" class="w-4 h-4 {{ ($check['status'] ?? '') === 'pass' ? 'text-green-600' : 'text-primary' }}"></i>
                                        <p class="text-sm font-semibold text-foreground">{{ $check['label'] ?? 'Resume check' }}</p>
                                    </div>
                                    <p class="text-xs text-muted mt-2">{{ $check['detail'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>

                        <h3 class="text-sm font-semibold text-foreground mb-3">Suggested improvements</h3>
                        <div class="space-y-3 mb-6">
                            @foreach($suggestions as $suggestion)
                                <div class="flex gap-3">
                                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-primary shrink-0 mt-0.5"></i>
                                    <p class="text-sm text-muted">{{ $suggestion }}</p>
                                </div>
                            @endforeach
                        </div>

                        @if(!empty($report['strengths']))
                            <h3 class="text-sm font-semibold text-foreground mb-3">Key Strengths</h3>
                            <div class="space-y-3 mb-6">
                                @foreach($report['strengths'] as $strength)
                                    <div class="flex gap-3">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0 mt-0.5"></i>
                                        <p class="text-sm text-muted">{{ $strength }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($report['missing_keywords']))
                            <h3 class="text-sm font-semibold text-foreground mb-3">Missing Keywords</h3>
                            <div class="flex flex-wrap gap-2 mb-6">
                                @foreach($report['missing_keywords'] as $keyword)
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">{{ $keyword }}</span>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="flex min-h-[320px] flex-col items-center justify-center text-center">
                            <i data-lucide="file-search" class="w-12 h-12 text-primary mb-4"></i>
                            <h2 class="text-xl font-bold text-foreground">Your report will appear here</h2>
                            <p class="text-sm text-muted max-w-sm mt-2">Upload a resume and the analyzer will prepare a readiness score, format checks, and practical next steps.</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Quick Stats --}}
        <section class="px-6 md:px-8 mb-12">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-card border border-border rounded-xl p-5 text-center">
                    <p class="text-2xl font-bold text-primary">6 sec</p>
                    <p class="text-xs text-muted mt-1">Average time recruiters spend on a resume</p>
                </div>
                <div class="bg-card border border-border rounded-xl p-5 text-center">
                    <p class="text-2xl font-bold text-primary">75%</p>
                    <p class="text-xs text-muted mt-1">Resumes rejected by ATS before human review</p>
                </div>
                <div class="bg-card border border-border rounded-xl p-5 text-center">
                    <p class="text-2xl font-bold text-primary">40%</p>
                    <p class="text-xs text-muted mt-1">More interviews with a tailored resume</p>
                </div>
            </div>
        </section>

        {{-- Resume Writing Tips --}}
        <section class="py-10 px-6 md:px-8">
            <h2 class="text-2xl font-bold text-foreground mb-2">Resume Writing Tips</h2>
            <p class="text-muted text-sm mb-8">Follow these proven strategies to make your resume stand out</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @php
                    $tips = [
                        ['icon' => 'target', 'title' => 'Tailor Your Resume to Each Job', 'desc' => 'Customize your resume for each application. Use keywords from the job description to pass ATS filters and show relevance.'],
                        ['icon' => 'bar-chart-2', 'title' => 'Quantify Your Achievements', 'desc' => 'Use numbers: "Increased revenue by 35%" is stronger than "improved sales." Metrics make your impact tangible.'],
                        ['icon' => 'scissors', 'title' => 'Keep It Concise and Relevant', 'desc' => 'One to two pages max. Focus on recent, relevant experience. Remove anything that doesn\'t support your current goals.'],
                        ['icon' => 'zap', 'title' => 'Use Action Verbs', 'desc' => 'Start bullets with "Led," "Built," "Optimized," "Launched." Action verbs show initiative and ownership.'],
                        ['icon' => 'check-circle', 'title' => 'Proofread Everything', 'desc' => 'One typo can cost you the interview. Read aloud, use spell-check, and have someone else review it.'],
                        ['icon' => 'layout', 'title' => 'Clean Formatting', 'desc' => 'Use consistent fonts, clear headings, and enough white space. Avoid tables, images, or fancy graphics that confuse ATS.'],
                    ];
                @endphp

                @foreach($tips as $tip)
                    <div class="bg-card border border-border rounded-xl p-5 flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                            <i data-lucide="{{ $tip['icon'] }}" class="w-5 h-5 text-primary"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-foreground mb-1">{{ $tip['title'] }}</h3>
                            <p class="text-xs text-muted leading-relaxed">{{ $tip['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Resume Sections Guide --}}
        <section class="py-10 px-6 md:px-8">
            <h2 class="text-2xl font-bold text-foreground mb-2">Essential Resume Sections</h2>
            <p class="text-muted text-sm mb-8">Make sure your resume includes these key sections</p>

            <div class="bg-card border border-border rounded-xl overflow-hidden">
                @php
                    $sections = [
                        ['name' => 'Contact Information', 'desc' => 'Full name, phone, email, LinkedIn, city/state. No full address needed.', 'required' => true],
                        ['name' => 'Professional Summary', 'desc' => '2-3 sentences highlighting your experience level, key skills, and what you bring to the role.', 'required' => true],
                        ['name' => 'Work Experience', 'desc' => 'Reverse chronological order. Company, title, dates, and 3-5 bullet points per role with achievements.', 'required' => true],
                        ['name' => 'Skills', 'desc' => 'Technical and soft skills relevant to the job. Match keywords from the job posting.', 'required' => true],
                        ['name' => 'Education', 'desc' => 'Degree, institution, graduation year. Include GPA only if above 3.5 and you\'re a recent graduate.', 'required' => true],
                        ['name' => 'Certifications', 'desc' => 'Relevant professional certifications, licenses, or completed courses.', 'required' => false],
                        ['name' => 'Projects', 'desc' => 'Personal or professional projects that demonstrate skills. Great for career changers or new graduates.', 'required' => false],
                    ];
                @endphp

                @foreach($sections as $index => $section)
                    <div class="flex items-start gap-4 p-5 {{ $index < count($sections) - 1 ? 'border-b border-border' : '' }}">
                        <div class="flex-shrink-0 mt-0.5">
                            @if($section['required'])
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-xs font-bold">{{ $index + 1 }}</span>
                            @else
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-secondary text-muted text-xs font-bold">+</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-semibold text-foreground">{{ $section['name'] }}</h3>
                                @if($section['required'])
                                    <span class="text-xs text-primary font-medium">Required</span>
                                @else
                                    <span class="text-xs text-muted">Optional</span>
                                @endif
                            </div>
                            <p class="text-xs text-muted mt-1">{{ $section['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Template Suggestions --}}
        <section class="py-10 px-6 md:px-8">
            <h2 class="text-2xl font-bold text-foreground mb-2">Template Suggestions</h2>
            <p class="text-muted text-sm mb-8">Pick a format that matches your industry and experience level</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $templates = [
                        ['name' => 'Classic Professional', 'for' => 'Finance, Law, Management', 'icon' => 'file-text', 'color' => 'bg-blue-100 text-blue-600'],
                        ['name' => 'Modern Minimalist', 'for' => 'Tech, Startups, Design', 'icon' => 'layout', 'color' => 'bg-purple-100 text-purple-600'],
                        ['name' => 'Creative Portfolio', 'for' => 'Marketing, Design, Media', 'icon' => 'palette', 'color' => 'bg-pink-100 text-pink-600'],
                        ['name' => 'Technical Specialist', 'for' => 'Engineering, IT, Data', 'icon' => 'code', 'color' => 'bg-emerald-100 text-emerald-600'],
                    ];
                @endphp

                @foreach($templates as $template)
                    <div class="bg-card border border-border rounded-xl p-5 text-center hover:border-primary/30 transition-colors">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl {{ $template['color'] }} mb-3">
                            <i data-lucide="{{ $template['icon'] }}" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-foreground mb-1">{{ $template['name'] }}</h3>
                        <p class="text-xs text-muted">{{ $template['for'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Common Mistakes --}}
        <section class="py-10 px-6 md:px-8">
            <h2 class="text-2xl font-bold text-foreground mb-2">Common Mistakes to Avoid</h2>
            <p class="text-muted text-sm mb-8">Don't let these errors cost you an interview</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @php
                    $mistakes = [
                        'Using a generic objective statement instead of a targeted summary',
                        'Including irrelevant work experience from 10+ years ago',
                        'Listing job duties instead of accomplishments',
                        'Using an unprofessional email address',
                        'Submitting the same resume for every application',
                        'Including personal info like age, photo, or marital status',
                    ];
                @endphp

                @foreach($mistakes as $mistake)
                    <div class="flex items-start gap-3 bg-card border border-border rounded-xl p-4">
                        <i data-lucide="x-circle" class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5"></i>
                        <p class="text-xs text-muted leading-relaxed">{{ $mistake }}</p>
                    </div>
                @endforeach
            </div>
        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('resume-analysis-form');
            const processing = document.getElementById('resume-processing');
            const button = document.getElementById('resume-analysis-button');

            if (!form || !processing || !button) {
                return;
            }

            form.addEventListener('submit', function () {
                processing.classList.remove('hidden');
                button.disabled = true;
                button.classList.add('opacity-75', 'cursor-not-allowed');
                button.innerHTML = '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span> Processing...';
            });
        });
    </script>
@endsection
