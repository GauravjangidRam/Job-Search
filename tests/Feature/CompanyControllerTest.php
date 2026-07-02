<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_companies_paginated_and_sorted_alphabetically(): void
    {
        Company::create(['name' => 'Zebra Corp', 'slug' => 'zebra-corp']);
        Company::create(['name' => 'Alpha Inc', 'slug' => 'alpha-inc']);
        Company::create(['name' => 'Mango Ltd', 'slug' => 'mango-ltd']);
        $response = $this->get('/companies');
        $response->assertStatus(200);
        $response->assertViewIs('companies.index');
        $response->assertViewHas('companies');

        $companies = $response->viewData('companies');
        $this->assertEquals('Alpha Inc', $companies->first()->name);
        $this->assertEquals('Zebra Corp', $companies->last()->name);
    }

    public function test_index_paginates_at_12_per_page(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Company::create(['name' => "Company {$i}", 'slug' => "company-{$i}"]);
        }
        $response = $this->get('/companies');
        $response->assertStatus(200);
        $companies = $response->viewData('companies');
        $this->assertCount(12, $companies);
        $this->assertEquals(2, $companies->lastPage());
    } 

    public function test_show_returns_company_with_job_listings(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);

        $olderJob = JobListing::create([
            'title' => 'Older Job',
            'company_name' => 'Test Company',
            'location' => 'Remote',
            'salary_min' => 50000,
            'salary_max' => 80000,
            'job_type' => 'Full-time',
            'location_type' => 'Remote',
            'description' => 'An older job listing',
            'skills' => ['PHP', 'Laravel'],
            'company_id' => $company->id,
        ]);
        $olderJob->created_at = now()->subDays(5);
        $olderJob->save();
        $newerJob = JobListing::create([
            'title' => 'Newer Job',
            'company_name' => 'Test Company',
            'location' => 'Remote',
            'salary_min' => 60000,
            'salary_max' => 90000,
            'job_type' => 'Full-time',
            'location_type' => 'Remote',
            'description' => 'A newer job listing',
            'skills' => ['React', 'TypeScript'],
            'company_id' => $company->id,
        ]);
        $newerJob->created_at = now();
        $newerJob->save();

        $response = $this->get('/companies/test-company');

        $response->assertStatus(200);
        $response->assertViewIs('companies.show');
        $response->assertViewHas('company');
        $response->assertViewHas('jobListings');

        $jobListings = $response->viewData('jobListings');
        $this->assertCount(2, $jobListings);
        $this->assertEquals('Newer Job', $jobListings->first()->title);
        $this->assertEquals('Older Job', $jobListings->last()->title);
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->get('/companies/nonexistent-company'); 
        $response->assertStatus(404);
        
    }
}
