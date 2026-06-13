@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1400px] mx-auto pt-16">
        <section aria-label="Hero" class="py-20 px-6 md:px-8">
            <x-home.hero-section :popularSearchTerms="$popularSearchTerms" :heroJob="$heroJob" :stats="$stats" />
        </section>

        <section aria-label="Job Discovery Filters" class="py-20 px-6 md:px-8">
            <x-home.job-discovery-filters :featuredJobs="$featuredJobs" />
        </section>
 
        <section aria-label="Featured Jobs" class="py-20 px-6 md:px-8">
            <x-home.featured-jobs :featuredJobs="$featuredJobs" />
        </section>

        <section aria-label="Company Showcase" class="py-20 px-6 md:px-8">
            <x-home.company-showcase :companies="$companies" />
        </section>

        <section aria-label="AI Resume Matching" class="py-20 px-6 md:px-8">
            <x-home.ai-resume-matching :aiFeatures="$aiFeatures" />
        </section>

        <section aria-label="Career Insights" class="py-20 px-6 md:px-8">
            <x-home.career-insights :careerInsights="$careerInsights" />
        </section>

        <section aria-label="Testimonials" class="py-20 px-6 md:px-8">
            <x-home.testimonials :testimonials="$testimonials" />
        </section>

        <section aria-label="Call to Action" class="py-20 px-6 md:px-8">
            <x-home.call-to-action />
        </section>
    </div>
@endsection
