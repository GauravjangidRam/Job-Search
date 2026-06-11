@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1200px] mx-auto pt-16">
        {{-- Page Header --}}
        <section class="py-16 px-6 md:px-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 mb-5">
                <i data-lucide="file-text" class="w-8 h-8 text-primary"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-3">Resume Resources</h1>
            <p class="text-muted max-w-xl mx-auto">Tips, templates, and best practices to help you craft a resume that stands out to employers.</p>
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

        {{-- CTA --}}
        <section class="py-10 px-6 md:px-8 mb-12">
            <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent border border-border rounded-xl p-8 md:p-10 text-center">
                <i data-lucide="sparkles" class="w-10 h-10 text-primary mx-auto mb-4"></i>
                <h2 class="text-xl md:text-2xl font-bold text-foreground mb-2">Ready to Apply?</h2>
                <p class="text-sm font-semibold text-foreground mb-3">Resume Builder Coming Soon</p>
                <p class="text-muted text-sm max-w-md mx-auto mb-6">Your resume is your first impression. Our guided builder is almost ready to help you create a polished resume before you apply.</p>
                <span class="inline-flex items-center gap-2 px-6 py-3 bg-secondary text-muted font-semibold rounded-lg cursor-not-allowed" aria-disabled="true">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    Coming Soon
                </span>
            </div>
        </section>
    </div>
@endsection
