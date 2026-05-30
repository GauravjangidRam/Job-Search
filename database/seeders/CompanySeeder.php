<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'name' => 'TechCorp Solutions',
                'logo_url' => 'https://ui-avatars.com/api/?name=TC&background=4F46E5&color=fff',
                'website_url' => 'https://techcorp.example.com',
                'description' => 'TechCorp Solutions is a leading software development company specializing in enterprise applications and cloud infrastructure.',
                'culture' => 'We foster a collaborative environment where innovation thrives. Our teams work in agile sprints with a strong emphasis on work-life balance, continuous learning, and open communication.',
                'employee_count' => 450,
                'founded_year' => 2012,
                'industry' => 'Technology',
                'is_hiring' => true,
                'metrics' => [
                    'Annual Revenue' => '$50M',
                    'Client Retention' => '95%',
                    'Employee Growth' => '30% YoY',
                ],
                'perks' => [
                    'Remote-first culture',
                    'Unlimited PTO',
                    'Annual learning budget of $3,000',
                    'Health and dental insurance',
                    'Stock options',
                ],
            ],
            [
                'name' => 'PixelWave Studios',
                'logo_url' => 'https://ui-avatars.com/api/?name=PW&background=EC4899&color=fff',
                'website_url' => 'https://pixelwave.example.com',
                'description' => 'PixelWave Studios creates stunning digital experiences for brands worldwide, from interactive websites to immersive mobile applications.',
                'culture' => 'Creativity is at the heart of everything we do. We encourage experimentation, celebrate diverse perspectives, and believe that great design comes from passionate teams.',
                'employee_count' => 120,
                'founded_year' => 2016,
                'industry' => 'Design',
                'is_hiring' => true,
                'metrics' => [
                    'Projects Delivered' => '500+',
                    'Client Satisfaction' => '4.9/5',
                    'Awards Won' => '25',
                ],
                'perks' => [
                    'Flexible working hours',
                    'Creative Friday projects',
                    'Home office stipend',
                    'Team retreats twice a year',
                ],
            ],
            [
                'name' => 'CloudNine Infrastructure',
                'logo_url' => 'https://ui-avatars.com/api/?name=CN&background=10B981&color=fff',
                'website_url' => 'https://cloudnine.example.com',
                'description' => 'CloudNine Infrastructure provides scalable cloud solutions and DevOps consulting for startups and enterprises alike.',
                'culture' => 'We are engineers at heart who love solving complex infrastructure challenges. Our culture values reliability, automation, and knowledge sharing through internal tech talks and documentation.',
                'employee_count' => 200,
                'founded_year' => 2014,
                'industry' => 'Cloud Computing',
                'is_hiring' => true,
                'metrics' => [
                    'Uptime SLA' => '99.99%',
                    'Servers Managed' => '10,000+',
                    'Cost Savings for Clients' => '40% avg',
                ],
                'perks' => [
                    'Certification reimbursement',
                    'On-call compensation',
                    '401(k) matching up to 6%',
                    'Gym membership',
                    'Parental leave',
                ],
            ],
            [
                'name' => 'DataMinds Analytics',
                'logo_url' => 'https://ui-avatars.com/api/?name=DM&background=06B6D4&color=fff',
                'website_url' => 'https://dataminds.example.com',
                'description' => 'DataMinds Analytics helps organizations unlock the power of their data through advanced analytics, machine learning, and business intelligence solutions.',
                'culture' => 'We are a data-driven team that values intellectual curiosity and rigorous analysis. We encourage publishing research, attending conferences, and contributing to open-source projects.',
                'employee_count' => 85,
                'founded_year' => 2018,
                'industry' => 'Data Science',
                'is_hiring' => true,
                'metrics' => [
                    'Models in Production' => '200+',
                    'Data Processed Daily' => '5TB',
                    'Prediction Accuracy' => '94%',
                ],
                'perks' => [
                    'Conference attendance budget',
                    'Flexible remote work',
                    'Research publication bonuses',
                    'Mental health support',
                ],
            ],
            [
                'name' => 'SecureNet Defense',
                'logo_url' => 'https://ui-avatars.com/api/?name=SN&background=DC2626&color=fff',
                'website_url' => 'https://securenet.example.com',
                'description' => 'SecureNet Defense is a cybersecurity firm providing threat detection, penetration testing, and security consulting to Fortune 500 companies.',
                'culture' => 'Security is a mindset, not just a job. We maintain a culture of vigilance, continuous training, and ethical hacking. Our red and blue teams regularly challenge each other to stay sharp.',
                'employee_count' => 150,
                'founded_year' => 2010,
                'industry' => 'Cybersecurity',
                'is_hiring' => false,
                'metrics' => [
                    'Threats Blocked' => '1M+ monthly',
                    'Response Time' => '<15 min',
                    'Certifications Held' => '50+',
                ],
                'perks' => [
                    'Security certification sponsorship',
                    'Annual CTF competitions',
                    'Top-tier hardware provided',
                    'Quarterly bonuses',
                ],
            ],
            [
                'name' => 'GreenLeaf Technologies',
                'logo_url' => 'https://ui-avatars.com/api/?name=GL&background=16A34A&color=fff',
                'website_url' => 'https://greenleaf.example.com',
                'description' => 'GreenLeaf Technologies builds software solutions for environmental monitoring, sustainability tracking, and clean energy management.',
                'culture' => 'We are passionate about using technology to fight climate change. Our team is mission-driven, collaborative, and committed to building a sustainable future through innovative software.',
                'employee_count' => 75,
                'founded_year' => 2019,
                'industry' => 'CleanTech',
                'is_hiring' => true,
                'metrics' => [
                    'Carbon Offset Tracked' => '2M tons',
                    'Partner Organizations' => '150+',
                    'Energy Savings Enabled' => '30%',
                ],
                'perks' => [
                    'Carbon-neutral office',
                    'Electric vehicle subsidy',
                    'Volunteer days for environmental causes',
                    'Plant-based catering',
                    'Bike-to-work program',
                ],
            ],
            [
                'name' => 'NexGen Digital',
                'logo_url' => 'https://ui-avatars.com/api/?name=NG&background=0891B2&color=fff',
                'website_url' => 'https://nexgen.example.com',
                'description' => 'NexGen Digital is a full-service e-commerce platform provider helping businesses launch and scale their online stores.',
                'culture' => 'We move fast and ship often. Our startup culture values ownership, transparency, and direct communication. Every team member has a voice in product decisions.',
                'employee_count' => 300,
                'founded_year' => 2015,
                'industry' => 'E-Commerce',
                'is_hiring' => true,
                'metrics' => [
                    'Merchants Served' => '10,000+',
                    'GMV Processed' => '$2B annually',
                    'Platform Uptime' => '99.95%',
                ],
                'perks' => [
                    'Equity for all employees',
                    'Unlimited snacks and beverages',
                    'Dog-friendly office',
                    'Annual company trip',
                    'Learning and development stipend',
                ],
            ],
            [
                'name' => 'AI Frontier Labs',
                'logo_url' => 'https://ui-avatars.com/api/?name=AF&background=7C3AED&color=fff',
                'website_url' => 'https://aifrontier.example.com',
                'description' => 'AI Frontier Labs pushes the boundaries of artificial intelligence research, developing cutting-edge models for NLP, computer vision, and autonomous systems.',
                'culture' => 'We are a research-first organization that values scientific rigor and bold experimentation. Our team publishes regularly at top conferences and collaborates with leading universities.',
                'employee_count' => 60,
                'founded_year' => 2020,
                'industry' => 'Artificial Intelligence',
                'is_hiring' => false,
                'metrics' => [
                    'Papers Published' => '45',
                    'Patents Filed' => '12',
                    'Model Accuracy Improvement' => '15% over baseline',
                ],
                'perks' => [
                    'GPU cluster access for personal projects',
                    'Sabbatical program',
                    'Conference travel budget',
                    'Flexible research hours',
                ],
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
