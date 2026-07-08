<?php

namespace App\Http\Controllers;

use App\Models\CareerInsight;
use App\Models\JobListing;
use Illuminate\View\View;

class CareerInsightController extends Controller
{
    public function index(): View
    {
        $grouped = collect();
        // 1. Fetch/Calculate Salary Data
        $salaryInsights = CareerInsight::where('type', 'salary')->orderBy('sort_order', 'asc')->get();
        if ($salaryInsights->isEmpty()) {
            $jobs = JobListing::active()->get();
            if ($jobs->count() >= 5) {
                // Group by title (top 5 titles)
                $averageSalaries = $jobs->groupBy(function($job) {
                    $titleLower = strtolower($job->title);
                    if (str_contains($titleLower, 'laravel') || str_contains($titleLower, 'php')) {
                        return 'Laravel Developer';
                    }
                    if (str_contains($titleLower, 'react') || str_contains($titleLower, 'frontend') || str_contains($titleLower, 'vue')) {
                        return 'Frontend Engineer';
                    }
                    if (str_contains($titleLower, 'devops') || str_contains($titleLower, 'cloud') || str_contains($titleLower, 'aws')) {
                        return 'DevOps Engineer';
                    }
                    if (str_contains($titleLower, 'design') || str_contains($titleLower, 'ux') || str_contains($titleLower, 'ui') || str_contains($titleLower, 'figma')) {
                        return 'UX/UI Designer';
                    }
                    if (str_contains($titleLower, 'data') || str_contains($titleLower, 'python') || str_contains($titleLower, 'ml') || str_contains($titleLower, 'machine')) {
                        return 'Data Scientist';
                    }
                    return $job->title;
                })->map(function($group) {
                    return $group->avg(fn($j) => ($j->salary_min + $j->salary_max) / 2);
                })->sortDesc()->take(5);

                $sort = 1;
                foreach ($averageSalaries as $title => $avgSalary) {
                    $salaryInsights->push(new CareerInsight([
                        'type' => 'salary',
                        'label' => $title,
                        'value' => (string) round($avgSalary),
                        'sort_order' => $sort++,
                    ]));
                }
            } else {
                $fallback = [
                    ['label' => 'Software Engineer', 'value' => '120000'],
                    ['label' => 'Product Manager', 'value' => '135000'],
                    ['label' => 'Data Scientist', 'value' => '145000'],
                    ['label' => 'UX/UI Designer', 'value' => '105000'],
                    ['label' => 'DevOps Engineer', 'value' => '140000'],
                ]; 
                foreach ($fallback as $index => $item) {
                    $salaryInsights->push(new CareerInsight([
                        'type' => 'salary',
                        'label' => $item['label'],
                        'value' => $item['value'],
                        'sort_order' => $index + 1,
                    ]));
                }
            }
        }
        $grouped['salary'] = $salaryInsights;

        // 2. Fetch/Calculate Trend Data
        $trendInsights = CareerInsight::where('type', 'trend')->orderBy('sort_order', 'asc')->get();
        if ($trendInsights->isEmpty()) {
            $jobs = JobListing::active()->get();
            if ($jobs->count() >= 5) {
                // Group by month created_at
                $monthlyCounts = $jobs->groupBy(function($job) {
                    return $job->created_at->format('F');
                })->map->count();

                $sort = 1;
                foreach ($monthlyCounts as $month => $count) {
                    $trendInsights->push(new CareerInsight([
                        'type' => 'trend',
                        'label' => $month,
                        'value' => (string) ($count * 50 + 1000),
                        'sort_order' => $sort++,
                    ]));
                }
            } else {
                $fallback = [
                    ['label' => 'January', 'value' => '1200'],
                    ['label' => 'February', 'value' => '1350'],
                    ['label' => 'March', 'value' => '1500'],
                    ['label' => 'April', 'value' => '1420'],
                    ['label' => 'May', 'value' => '1600'],
                    ['label' => 'June', 'value' => '1750'],
                ];
                foreach ($fallback as $index => $item) {
                    $trendInsights->push(new CareerInsight([
                        'type' => 'trend',
                        'label' => $item['label'],
                        'value' => $item['value'],
                        'sort_order' => $index + 1,
                    ]));
                }
            }
        }
        $grouped['trend'] = $trendInsights;

        // 3. Fetch/Calculate Skill Data
        $skillInsights = CareerInsight::where('type', 'skill')->orderBy('sort_order', 'asc')->get();
        if ($skillInsights->isEmpty()) {
            $jobs = JobListing::active()->get();
            $skillsData = $jobs->pluck('skills')->filter();
            $totalJobs = $jobs->count();

            if ($totalJobs > 0 && $skillsData->count() > 0) {
                $skillCounts = [];
                foreach ($skillsData as $skills) {
                    if (is_array($skills)) {
                        foreach ($skills as $skill) {
                            $skill = trim($skill);
                            if ($skill !== '') {
                                $skillCounts[$skill] = ($skillCounts[$skill] ?? 0) + 1;
                            }
                        }
                    }
                }
                arsort($skillCounts);
                $topSkills = array_slice($skillCounts, 0, 5, true);

                $sort = 1;
                foreach ($topSkills as $skill => $count) {
                    $percentage = round(($count / $totalJobs) * 100);
                    $skillInsights->push(new CareerInsight([
                        'type' => 'skill',
                        'label' => $skill,
                        'value' => (string) $percentage,
                        'sort_order' => $sort++,
                    ]));
                }
            } else {
                $fallback = [
                    ['label' => 'JavaScript', 'value' => '85'],
                    ['label' => 'Python', 'value' => '78'],
                    ['label' => 'React', 'value' => '72'],
                    ['label' => 'AWS', 'value' => '68'],
                    ['label' => 'SQL', 'value' => '65'],
                ];
                foreach ($fallback as $index => $item) {
                    $skillInsights->push(new CareerInsight([
                        'type' => 'skill',
                        'label' => $item['label'],
                        'value' => $item['value'],
                        'sort_order' => $index + 1,
                    ]));
                }
            }
        }
        $grouped['skill'] = $skillInsights;

        return view('insights.index', [
            'insights' => $grouped,
        ]);
    }
}
