# Implementation Plan: Dynamic Pages and Data

## Overview

This plan transforms the Job Hub Laravel application from a static-data prototype into a fully dynamic, database-driven platform. Implementation proceeds in layers: database schema first, then models and relationships, then controllers and views, then search/application flow, and finally seeders and wiring.

## Tasks

- [x] 1. Create database migrations and Eloquent models
  - [x] 1.1 Create the companies table migration and Company model
    - Create migration `create_companies_table` with all columns defined in the design (name, slug, logo_url, website_url, description, culture, employee_count, founded_year, industry, is_hiring, metrics, perks, timestamps)
    - Add indexes on `slug` (unique), `is_hiring`, and `industry`
    - Create `App\Models\Company` with fillable, casts, `hasMany(JobListing)` relationship, and slug auto-generation in the `booted()` method using `Str::slug` with uniqueness suffix logic
    - _Requirements: 1.1, 1.2, 1.3, 1.4_

  - [x] 1.2 Create the testimonials table migration and Testimonial model
    - Create migration `create_testimonials_table` with columns: name, role, company, avatar_url, text, rating, is_featured, timestamps
    - Add index on `is_featured`
    - Create `App\Models\Testimonial` with fillable, casts for rating (integer) and is_featured (boolean)
    - _Requirements: 2.1, 2.3_

  - [x] 1.3 Create the career_insights table migration and CareerInsight model
    - Create migration `create_career_insights_table` with columns: type (enum: salary, trend, skill), label, value, sort_order, timestamps
    - Add indexes on `type` and composite (`type`, `sort_order`)
    - Create `App\Models\CareerInsight` with fillable and casts
    - _Requirements: 3.1, 3.3_

  - [x] 1.4 Create the job_applications table migration and JobApplication model
    - Create migration `create_job_applications_table` with columns: user_id (FK), job_listing_id (FK), timestamps
    - Add unique constraint on (`user_id`, `job_listing_id`)
    - Create `App\Models\JobApplication` with fillable, `belongsTo(User)` and `belongsTo(JobListing)` relationships
    - _Requirements: 9.5, 9.7_

  - [x] 1.5 Create migration to add company_id foreign key to job_listings table
    - Create migration `add_company_id_to_job_listings_table` adding nullable `company_id` column with foreign key referencing `companies.id` with `ON DELETE SET NULL`
    - Update `App\Models\JobListing` to add `company_id` to fillable and add `belongsTo(Company)` relationship
    - _Requirements: 1.5, 1.6_

  - [ ]* 1.6 Write property tests for Company slug generation
    - **Property 1: Slug format correctness** — For any company name, the generated slug is lowercase, uses only alphanumeric characters and single hyphens, no consecutive hyphens, and does not start or end with a hyphen
    - **Property 2: Slug uniqueness under duplication** — For N companies with the same name, all N slugs are distinct with incrementing suffixes
    - **Validates: Requirements 1.2, 1.3**

  - [ ]* 1.7 Write property tests for Company JSON round-trip and cascade deletion
    - **Property 3: JSON metrics and perks round-trip** — Storing and reloading metrics/perks produces identical values
    - **Property 4: Cascade null on company deletion** — Deleting a company sets company_id to null on all associated job listings
    - **Validates: Requirements 1.4, 1.6**

  - [ ]* 1.8 Write property tests for Testimonial validation
    - **Property 6: Testimonial rating validation** — Rating is accepted if and only if it is between 1 and 5 inclusive
    - **Validates: Requirements 2.3, 2.4**

- [x] 2. Checkpoint - Ensure all migrations run and models are correct
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Create database seeders
  - [x] 3.1 Create CompanySeeder
    - Create `database/seeders/CompanySeeder.php` that seeds at least 6 companies with all required fields (name, logo_url, culture, metrics with 2+ entries, perks with 2+ entries, is_hiring)
    - _Requirements: 12.1_

  - [x] 3.2 Create TestimonialSeeder
    - Create `database/seeders/TestimonialSeeder.php` that seeds at least 6 testimonials with all required fields (name, role, avatar_url, text max 200 chars, rating 1-5), with some marked as `is_featured`
    - _Requirements: 12.2_

  - [x] 3.3 Create CareerInsightSeeder
    - Create `database/seeders/CareerInsightSeeder.php` that seeds at least 4 salary records, 6 trend records, and 5 skill records with appropriate sort_order values
    - _Requirements: 12.3_

  - [x] 3.4 Update DatabaseSeeder and associate job listings with companies
    - Register CompanySeeder, TestimonialSeeder, and CareerInsightSeeder in `DatabaseSeeder`
    - After seeding companies, update existing job listings to reference seeded companies via `company_id`
    - _Requirements: 12.4, 12.5_

- [x] 4. Implement CompanyController and company pages
  - [x] 4.1 Create CompanyController with index and show methods
    - Create `App\Http\Controllers\CompanyController` with `index()` method returning paginated companies (12 per page, alphabetical by name) and `show($slug)` method loading a company by slug with associated job listings ordered by `created_at` desc
    - Return 404 if slug not found
    - _Requirements: 4.1, 4.2, 5.1, 5.2, 5.3_

  - [x] 4.2 Create companies/index.blade.php view
    - Create `resources/views/companies/index.blade.php` displaying company cards with name, logo, industry, employee count, hiring status
    - Add Alpine.js filtering by industry and hiring status (client-side)
    - Add pagination controls showing current page, total pages, next/previous navigation
    - Display empty-state message when no companies match filters with option to clear filters
    - _Requirements: 4.1, 4.2, 4.4, 4.5_

  - [x] 4.3 Create companies/show.blade.php view
    - Create `resources/views/companies/show.blade.php` displaying company name, logo, description, culture, metrics as label-value pairs, perks list (up to 10 items)
    - Display associated job listings with title, location, job type, location type, salary range
    - Show "no open positions" message if company has no job listings
    - _Requirements: 5.1, 5.2, 5.4_

  - [x] 4.4 Create the company-logo Blade component
    - Create `resources/views/components/company-logo.blade.php` that renders the company logo at 48×48px with object-fit contain
    - If logo_url is null/empty, generate a UI Avatars placeholder URL using first letters of company name (up to 2 initials) with deterministic background color
    - Add `onerror` JavaScript handler to swap to the placeholder on load failure
    - _Requirements: 11.1, 11.2, 11.3, 11.4_

  - [ ]* 4.5 Write property tests for companies pagination and logo placeholder
    - **Property 8: Companies alphabetical pagination** — For N companies, listing returns them sorted alphabetically with correct page sizes and total page count
    - **Property 9: Company placeholder generation determinism** — Same company name always produces the same placeholder URL
    - **Validates: Requirements 4.1, 4.2, 4.3, 11.2**

  - [ ]* 4.6 Write property tests for company detail page
    - **Property 10: Company jobs ordering** — For a company with N job listings, they are displayed ordered by created_at descending
    - **Validates: Requirements 5.2**

- [x] 5. Implement CareerInsightController and insights page
  - [x] 5.1 Create CareerInsightController with index method
    - Create `App\Http\Controllers\CareerInsightController` with `index()` method that queries career_insights grouped by type, ordered by sort_order ascending within each group, limited to 20 per type
    - _Requirements: 3.2, 6.1_

  - [x] 5.2 Create insights/index.blade.php view
    - Create `resources/views/insights/index.blade.php` with three sections: salary data (bar chart, up to 10 roles with dollar-formatted values), hiring trends (line chart, up to 12 monthly data points), in-demand skills (progress bars, up to 10 skills with percentage)
    - Include accessible fallback content (tables/lists) if chart rendering fails
    - Display "no data available" message for empty sections
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ]* 5.3 Write property test for career insights grouping
    - **Property 7: Career insights grouping and ordering** — Grouping by type produces correct groups, each ordered by sort_order ascending, max 20 per group
    - **Validates: Requirements 3.2, 10.4**

- [x] 6. Implement ResumeController and resume page
  - [x] 6.1 Create ResumeController with index method and resume view
    - Create `App\Http\Controllers\ResumeController` with `index()` returning the resume view
    - Create `resources/views/resume/index.blade.php` with: resume tips section (at least 3 tips with heading and description), template suggestions section (at least 3 templates with name and style description), and a "coming soon" call-to-action section that does not navigate away or trigger builder functionality
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 7. Implement home page search and dynamic data
  - [x] 7.1 Refactor HomeController to use database queries
    - Replace all hardcoded data methods in `HomeController` with Eloquent queries:
      - Featured jobs: `JobListing::orderBy('created_at', 'desc')->limit(6)->get()`
      - Companies: `Company::where('is_hiring', true)->orderBy('name')->limit(6)->get()`
      - Testimonials: `Testimonial::where('is_featured', true)->orderBy('created_at', 'desc')->limit(6)->get()`
      - Career insights: `CareerInsight::orderBy('sort_order')->get()->groupBy('type')`
    - Ensure empty collections are passed to the view when tables are empty
    - Remove all static data arrays and unused DTO imports
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

  - [x] 7.2 Wire home page hero search to JobController
    - Update the home page hero search form to submit to `/jobs` with `search` query parameter
    - Add client-side validation: reject empty/whitespace-only input with inline error message, do not navigate
    - Validate max 100 characters for the search input
    - Wire popular search term clicks to populate the search input without navigating
    - _Requirements: 8.1, 8.2, 8.5_

  - [ ]* 7.3 Write property tests for search and home page queries
    - **Property 11: Search input handling** — Non-empty strings redirect to /jobs?search={term}; empty/whitespace strings are rejected
    - **Property 12: Search filter correctness** — All results contain the search term in title, company_name, or description; no matching listings are excluded
    - **Property 14: Home page query limits** — At most 6 job listings and 6 hiring companies are returned
    - **Validates: Requirements 8.1, 8.2, 8.3, 10.1, 10.2**

  - [ ]* 7.4 Write property test for featured testimonials
    - **Property 5: Featured testimonials query correctness** — Only featured testimonials are returned, ordered by created_at desc, max 6
    - **Validates: Requirements 2.2, 10.3**

- [x] 8. Checkpoint - Ensure all pages render correctly with seeded data
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Implement job application flow
  - [x] 9.1 Add apply and submitApplication methods to JobController
    - Add `apply(JobListing $job)` GET method: check authentication (redirect to login if not), check for existing application, display apply page with job details
    - Add `submitApplication(JobListing $job)` POST method: validate authenticated user, check for duplicate application, create `JobApplication` record, redirect with success message
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7_

  - [x] 9.2 Create jobs/apply.blade.php view
    - Create `resources/views/jobs/apply.blade.php` displaying job title, company name, salary range, location, description, skills
    - Show confirmation section with "Confirm Application" button
    - If already applied, show "already applied" message and disable the button
    - Display success message after submission
    - _Requirements: 9.2, 9.4, 9.5, 9.7_

  - [ ]* 9.3 Write property test for duplicate application prevention
    - **Property 13: Duplicate application prevention** — A user who has already applied sees "already applied" indicator and confirm button is disabled
    - **Validates: Requirements 9.7**

- [x] 10. Register all routes and create empty-state component
  - [x] 10.1 Register all new routes in web.php
    - Add routes for companies (index, show), insights, resume, and job application (apply GET, submitApplication POST with auth middleware)
    - _Requirements: 4.1, 5.1, 6.1, 7.1, 9.1, 9.3_

  - [x] 10.2 Create the empty-state Blade component
    - Create `resources/views/components/empty-state.blade.php` as a reusable empty-state message block accepting a message prop
    - _Requirements: 4.5, 5.4, 6.5_

- [x] 11. Update navigation links in the shared layout
  - [x] 11.1 Update app.blade.php navigation
    - Add links to Companies (/companies), Career Insights (/insights), and Resume (/resume) in the main navigation bar
    - Add "Apply Now" button/link on the job detail page pointing to `/jobs/{job}/apply`
    - _Requirements: 4.1, 6.1, 7.1, 9.1_

- [x] 12. Final checkpoint - Ensure all tests pass and application works end-to-end
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- The existing `JobController` already handles search filtering — the home page just needs to wire the form to it
- All views should use the existing `layouts/app.blade.php` layout
- Alpine.js is already available in the project for client-side interactivity

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3", "1.4"] },
    { "id": 1, "tasks": ["1.5", "1.6", "1.7", "1.8"] },
    { "id": 2, "tasks": ["3.1", "3.2", "3.3"] },
    { "id": 3, "tasks": ["3.4"] },
    { "id": 4, "tasks": ["4.1", "5.1", "6.1", "10.1", "10.2"] },
    { "id": 5, "tasks": ["4.2", "4.3", "4.4", "5.2", "7.1"] },
    { "id": 6, "tasks": ["4.5", "4.6", "5.3", "7.2", "9.1"] },
    { "id": 7, "tasks": ["7.3", "7.4", "9.2", "9.3"] },
    { "id": 8, "tasks": ["11.1"] }
  ]
}
```
