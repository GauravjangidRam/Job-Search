<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\FeatureData;
use App\DataTransferObjects\FooterColumnData;
use App\Models\CareerInsight;
use App\Models\Company;
use App\Models\JobListing;
use App\Models\Testimonial;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredJobs = JobListing::where('status', 'active')->orderBy('created_at', 'desc')->limit(6)->get();
        $heroJob = $featuredJobs->first();
        return view('home', [
            'popularSearchTerms' => $this->getPopularSearchTerms(),
            'featuredJobs'       => $featuredJobs,
            'heroJob'            => $heroJob,
            'companies'          => Company::where('verification_status', 'approved')->orderByDesc('is_hiring')->orderBy('name')->limit(6)->get(),
            'testimonials'       => Testimonial::where('is_featured', true)->orderBy('created_at', 'desc')->limit(6)->get(),
            'careerInsights'     => CareerInsight::orderBy('sort_order')->get()->groupBy('type'),
            'aiFeatures'         => $this->getAiFeatures(),
            'footerLinks'        => $this->getFooterLinks(),
            'stats'              => $this->getStats(),
        ]);
    }
    /**
     * Get platform statistics for the homepage.
     */
    private function getStats(): array
    {
        return Cache::remember('home:stats:v1', now()->addMinutes(5), fn () => [
            'jobs' => JobListing::active()->count(),
            'companies' => Company::where('verification_status', 'approved')->count(),
            'applications' => \App\Models\JobApplication::count(),
        ]);
    }

    /**
     * Get popular search terms dynamically from job listings skills and titles.
     *
     * @return array<string>
     */
    private function getPopularSearchTerms(): array
    {
        return Cache::remember('home:popular-search-terms:v1', now()->addMinutes(10), function (): array {
        // Get the most common skills from active job listings
        $jobs = JobListing::active()->pluck('skills')->filter();

        $skillCounts = [];
        foreach ($jobs as $skills) {
            if (is_array($skills)) {
                foreach ($skills as $skill) {
                    $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
                }
            }
        }
        arsort($skillCounts);
        $topSkills = array_slice(array_keys($skillCounts), 0, 5);

        // If not enough skills, supplement with job types
        if (count($topSkills) < 3) {
            $topSkills = ['Software Engineer', 'Product Designer', 'Data Scientist', 'Frontend Developer', 'Marketing Manager'];
        }
        return $topSkills;
        });
    }

    /**
     * @return array<FeatureData>
     */
    private function getAiFeatures(): array
    {
        return [
            new FeatureData(
                icon: 'sparkles',
                title: 'Personalized Recommendations',
                description: 'AI analyzes your skills and experience to surface roles that match your unique career profile.',
            ), 
            new FeatureData(
                icon: 'target',
                title: 'Match Scores',
                description: 'See how well you fit each role with a compatibility score based on your resume and preferences.',
            ),
            new FeatureData(
                icon: 'file-text',
                title: 'Resume Optimization',
                description: 'Get actionable suggestions to improve your resume and increase your chances of landing interviews.',
            ),
        ];
    }

    /**
     * @return array<FooterColumnData>
     */
    private function getFooterLinks(): array
    { 
        return [
            new FooterColumnData(
                heading: 'For Job Seekers',
                links: [
                    ['label' => 'Browse Jobs', 'url' => '/jobs'],
                    ['label' => 'Career Advice', 'url' => '/advice'],
                    ['label' => 'Resume Builder', 'url' => '/resume'],
                    ['label' => 'Salary Calculator', 'url' => '/salary'],
                ],
            ),
            new FooterColumnData(
                heading: 'For Employers',
                links: [
                    ['label' => 'Post a Job', 'url' => '/post-job'],
                    ['label' => 'Talent Search', 'url' => '/talent'],
                    ['label' => 'Pricing', 'url' => '/pricing'],
                    ['label' => 'Employer Branding', 'url' => '/branding'],
                ],
            ),
            new FooterColumnData(
                heading: 'Company',
                links: [
                    ['label' => 'About Us', 'url' => '/about'],
                    ['label' => 'Blog', 'url' => '/blog'],
                    ['label' => 'Contact', 'url' => '/contact'],
                    ['label' => 'Privacy Policy', 'url' => '/privacy'],
                ],
            ),
        ];
    }
}
