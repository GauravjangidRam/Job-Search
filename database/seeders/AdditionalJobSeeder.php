<?php

namespace Database\Seeders;

use App\Models\JobListing;
use Illuminate\Database\Seeder;

class AdditionalJobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Software Developer',
                'company_name' => 'TechCorp Solutions',
                'company_logo_url' => 'https://ui-avatars.com/api/?name=TC&background=4F46E5&color=fff',
                'location' => 'Bengaluru, India',
                'salary_min' => 800000,
                'salary_max' => 1400000,
                'job_type' => 'Full-time',
                'location_type' => 'Hybrid',
                'description' => 'Develop and maintain web applications using PHP (Laravel) and modern frontend frameworks. Collaborate with cross-functional teams to deliver high-quality software.',
                'skills' => ['PHP', 'Laravel', 'JavaScript', 'MySQL'],
                'created_at' => now(),
            ],
            [
                'title' => 'UI/UX Designer',
                'company_name' => 'PixelWave Studios',
                'company_logo_url' => 'https://ui-avatars.com/api/?x=PW&background=EC4899&color=fff',
                'location' => 'Remote, India',
                'salary_min' => 500000,
                'salary_max' => 900000,
                'job_type' => 'Full-time',
                'location_type' => 'Remote',
                'description' => 'Design user-centred interfaces and produce high-fidelity prototypes. Work closely with product and engineering teams to iterate on UX solutions.',
                'skills' => ['Figma', 'User Research', 'Prototyping', 'Adobe XD'],
                'created_at' => now(),
            ],
            [
                'title' => 'Business Analyst',
                'company_name' => 'DataMinds Analytics',
                'company_logo_url' => 'https://ui-avatars.com/api/?name=DM&background=06B6D4&color=fff',
                'location' => 'Hyderabad, India',
                'salary_min' => 600000,
                'salary_max' => 1000000,
                'job_type' => 'Full-time',
                'location_type' => 'On-site',
                'description' => 'Analyze business requirements, translate them into technical specs, and partner with data and engineering teams to deliver analytics-driven solutions.',
                'skills' => ['SQL', 'Data Analysis', 'Stakeholder Management', 'Excel'],
                'created_at' => now(),
            ],
        ];

        foreach ($jobs as $job) {
            JobListing::create($job);
        }
    }
}
