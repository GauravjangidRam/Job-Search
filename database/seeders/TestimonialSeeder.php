<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Sarah Chen',
                'role' => 'Software Engineer',
                'company' => 'TechCorp',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Sarah+Chen&background=4f46e5&color=fff',
                'text' => 'Job Hub helped me land my dream role in just two weeks. The search filters made it easy to find positions that matched my skills perfectly.',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'name' => 'Marcus Johnson',
                'role' => 'Product Manager',
                'company' => 'InnovateLabs',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Marcus+Johnson&background=059669&color=fff',
                'text' => 'The company profiles gave me great insight into workplace culture before I even applied. I found a team that truly fits my values.',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'name' => 'Emily Rodriguez',
                'role' => 'UX Designer',
                'company' => 'DesignFlow',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Emily+Rodriguez&background=dc2626&color=fff',
                'text' => 'I appreciated how straightforward the application process was. No endless forms, just a clean confirmation and I was done.',
                'rating' => 4,
                'is_featured' => true,
            ],
            [
                'name' => 'David Park',
                'role' => 'Data Analyst',
                'company' => 'DataDriven Inc',
                'avatar_url' => 'https://ui-avatars.com/api/?name=David+Park&background=7c3aed&color=fff',
                'text' => 'The career insights section helped me understand salary trends in my field. I negotiated a better offer thanks to that data.',
                'rating' => 5,
                'is_featured' => true,
            ],
            [
                'name' => 'Aisha Patel',
                'role' => 'DevOps Engineer',
                'company' => 'CloudScale',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Aisha+Patel&background=0891b2&color=fff',
                'text' => 'Great platform with quality job listings. I found several remote positions that matched my experience level within days of signing up.',
                'rating' => 4,
                'is_featured' => false,
            ],
            [
                'name' => 'James Wilson',
                'role' => 'Frontend Developer',
                'company' => 'WebWorks',
                'avatar_url' => 'https://ui-avatars.com/api/?name=James+Wilson&background=ca8a04&color=fff',
                'text' => 'Solid job board with a clean interface. The filtering options could use more granularity, but overall a good experience.',
                'rating' => 3,
                'is_featured' => false,
            ],
            [
                'name' => 'Lisa Thompson',
                'role' => 'Engineering Manager',
                'company' => 'ScaleUp',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Lisa+Thompson&background=be185d&color=fff',
                'text' => 'As a hiring manager, I love how Job Hub presents company culture. It attracts candidates who are genuinely interested in our mission.',
                'rating' => 5,
                'is_featured' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
