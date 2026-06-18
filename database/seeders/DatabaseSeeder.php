<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            CompanySeeder::class,
            JobListingSeeder::class,
            AdditionalJobSeeder::class,
            TestimonialSeeder::class,
            CareerInsightSeeder::class,
        ]);

        // Associate job listings with seeded companies by matching company_name
        $companies = Company::all()->keyBy('name');

        JobListing::all()->each(function (JobListing $jobListing) use ($companies) {
            if ($companies->has($jobListing->company_name)) {
                $jobListing->update([
                    'company_id' => $companies->get($jobListing->company_name)->id,
                ]);
            }
        });
    }
}
