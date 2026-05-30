# Requirements Document

## Introduction

This feature transforms the Job Hub application from a static-data prototype into a fully dynamic, database-driven platform. It introduces new database tables (companies, testimonials, career insights), creates dedicated pages (Companies, Company Detail, Career Insights, Resume), wires the home page search to actual job results, and adds a job application flow. All hardcoded data in the HomeController is replaced with database queries.

## Glossary

- **Job_Hub**: The Laravel-based job search web application
- **Company**: An employer entity stored in the companies database table with name, logo, description, culture, metrics, perks, and hiring status
- **Job_Listing**: A job posting stored in the job_listings database table
- **Testimonial**: A user review stored in the testimonials database table
- **Career_Insight**: Salary, trend, and skill data stored in the career_insights database table
- **Home_Page**: The root route (/) displaying featured jobs, companies, testimonials, and career insights
- **Search_Handler**: The component that processes search queries from the home page hero section and redirects to filtered job results
- **Company_Controller**: The controller responsible for listing companies and showing company detail pages
- **Job_Controller**: The existing controller handling job listing and detail views
- **Application_Page**: A dedicated page where authenticated users can review job details and submit an application

## Requirements

### Requirement 1: Companies Database Table

**User Story:** As a developer, I want a companies table in the database, so that company data is persistent, queryable, and not hardcoded in the controller.

#### Acceptance Criteria

1. THE Job_Hub SHALL have a companies database table with columns: id (auto-incrementing primary key), name (string, max 255 characters, required), slug (string, max 255 characters, unique, required), logo_url (string, max 2048 characters, nullable), website_url (string, max 2048 characters, nullable), description (text, nullable), culture (text, nullable), employee_count (unsigned integer, nullable), founded_year (unsigned integer between 1800 and the current year, nullable), industry (string, max 100 characters, nullable), is_hiring (boolean, default false), metrics (JSON, nullable), perks (JSON, nullable), created_at (timestamp), and updated_at (timestamp)
2. WHEN a Company record is created, THE Job_Hub SHALL generate a slug from the company name by converting it to lowercase, replacing spaces and special characters with hyphens, and removing consecutive hyphens
3. IF a generated slug already exists in the companies table, THEN THE Job_Hub SHALL append a numeric suffix (e.g., -2, -3) to produce a unique slug
4. THE Job_Hub SHALL store company metrics as a JSON object in the metrics column and company perks as a JSON array of strings (max 50 items, each string max 255 characters) in the perks column
5. THE Job_Hub SHALL define a nullable company_id foreign key column on the job_listings table that references the companies table id column
6. IF a Company record is deleted, THEN THE Job_Hub SHALL set the company_id to null on all associated job_listings records

### Requirement 2: Testimonials Database Table

**User Story:** As a developer, I want a testimonials table in the database, so that testimonial content can be managed dynamically.

#### Acceptance Criteria

1. THE Job_Hub SHALL have a testimonials database table with columns: id (auto-incrementing primary key), name (string, max 100 characters, required), role (string, max 100 characters, required), company (string, max 100 characters, required), avatar_url (string, max 2048 characters, nullable), text (text, max 1000 characters, required), rating (unsigned integer, required), is_featured (boolean, default false), and timestamps (created_at, updated_at)
2. WHEN testimonials are displayed on the Home_Page, THE Job_Hub SHALL query only testimonials where is_featured is true, ordered by created_at descending, limited to a maximum of 6 results
3. THE Job_Hub SHALL validate that rating is an integer between 1 and 5 inclusive
4. IF a testimonial is submitted with a rating value outside the range of 1 to 5 or with any required field (name, role, company, text) empty, THEN THE Job_Hub SHALL reject the record and return a validation error message indicating which field failed validation

### Requirement 3: Career Insights Database Table

**User Story:** As a developer, I want career insights data stored in the database, so that salary data, hiring trends, and in-demand skills can be updated without code changes.

#### Acceptance Criteria

1. THE Job_Hub SHALL have a career_insights database table with columns: id (auto-incrementing primary key), type (enum: salary, trend, skill), label (string, maximum 100 characters), value (string, maximum 255 characters), sort_order (unsigned integer), and timestamps (created_at, updated_at)
2. WHEN the Home_Page loads career insights, THE Job_Hub SHALL group records by type, order them by sort_order ascending within each group, and return a maximum of 20 records per type
3. THE Job_Hub SHALL support exactly three insight types as enum values: salary, trend, and skill
4. IF no career_insights records exist for a given type, THEN THE Job_Hub SHALL return an empty collection for that type group without producing an error

### Requirement 4: Companies Listing Page

**User Story:** As a job seeker, I want a dedicated companies page, so that I can browse all companies on the platform.

#### Acceptance Criteria

1. WHEN a user navigates to /companies, THE Job_Hub SHALL display a paginated list of all companies sorted alphabetically by name, showing each company's name, logo, industry, employee count, and hiring status
2. WHEN companies are displayed, THE Job_Hub SHALL show 12 companies per page with pagination controls indicating the current page number, total pages, and navigation to the next and previous pages
3. IF a company logo_url is null or returns a loading error, THEN THE Job_Hub SHALL display a generated placeholder showing the first letter of each word in the company name, up to a maximum of 2 characters
4. THE Job_Hub SHALL allow filtering companies by industry and hiring status on the companies page, applying selected filters immediately without requiring a page reload
5. IF the applied filters return no matching companies, THEN THE Job_Hub SHALL display an empty-state message indicating that no companies match the selected filters and provide an option to clear all filters

### Requirement 5: Company Detail Page

**User Story:** As a job seeker, I want to view a company profile page, so that I can learn about a company before applying to their jobs.

#### Acceptance Criteria

1. WHEN a user navigates to /companies/{slug}, THE Job_Hub SHALL display the company name, logo, description, culture, metrics (as label-value pairs), and perks (as a list of up to 10 items)
2. WHEN a company detail page loads, THE Job_Hub SHALL display all Job_Listing records associated with that company, ordered by most recently created first, showing each listing's title, location, job type, location type, and salary range
3. IF a company slug does not exist in the database, THEN THE Job_Hub SHALL return a 404 page indicating that the requested company was not found
4. IF a company has no associated Job_Listing records, THEN THE Job_Hub SHALL display a message indicating that no open positions are currently available for that company

### Requirement 6: Career Insights Page

**User Story:** As a job seeker, I want a dedicated career insights page, so that I can explore salary data, hiring trends, and in-demand skills in detail.

#### Acceptance Criteria

1. WHEN a user navigates to /insights, THE Job_Hub SHALL display three sections: salary data, hiring trends, and in-demand skills, with data retrieved from the career_insights table
2. THE Job_Hub SHALL present salary data as a list of up to 10 roles, each showing the role title and its corresponding annual salary value formatted as a dollar amount
3. THE Job_Hub SHALL present hiring trends as up to 12 monthly data points, each showing the month label and the number of job postings for that month
4. THE Job_Hub SHALL present up to 10 in-demand skills, each showing the skill name and a percentage indicator (0–100) representing the proportion of job listings requiring that skill
5. IF the career_insights table contains no data for a section, THEN THE Job_Hub SHALL display a message indicating that no data is currently available for that section
6. WHEN the page loads, THE Job_Hub SHALL render salary data as a bar chart, hiring trends as a line chart, and in-demand skills as labeled progress bars, with accessible fallback content displayed if chart rendering fails

### Requirement 7: Resume Page

**User Story:** As a job seeker, I want a dedicated resume page, so that I can access resume-related tools and information.

#### Acceptance Criteria

1. WHEN a user navigates to /resume, THE Job_Hub SHALL return an HTTP 200 response and display a resume landing page using the application's shared layout including the navigation bar
2. THE Job_Hub SHALL display a resume tips section containing at least 3 actionable resume writing tips, each with a heading and a description
3. THE Job_Hub SHALL display a template suggestions section containing at least 3 resume template suggestions, each with a name and a brief description of the template style
4. THE Job_Hub SHALL display a call-to-action section that indicates the resume builder feature is coming soon and includes a visible heading and a descriptive message informing users the functionality is not yet available
5. IF a user interacts with the resume builder call-to-action, THEN THE Job_Hub SHALL not navigate away from the page or trigger any builder functionality

### Requirement 8: Home Page Search Functionality

**User Story:** As a job seeker, I want the home page search to produce real results, so that I can find jobs matching my query.

#### Acceptance Criteria

1. WHEN a user submits the hero search form with a non-empty job title (maximum 100 characters), THE Search_Handler SHALL redirect to /jobs with the job title passed as the "search" query parameter and, if provided, the location passed as the "location" query parameter
2. IF a user submits the hero search form with an empty or whitespace-only job title, THEN THE Search_Handler SHALL display an inline validation error below the search input and SHALL NOT navigate away from the Home_Page
3. WHEN the /jobs page receives a "search" query parameter, THE Job_Controller SHALL filter job listings by performing a case-insensitive partial match against the title, company_name, or description fields and return results ordered by newest first, paginated at 12 per page
4. IF the search query returns zero matching job listings, THEN THE Job_Controller SHALL display the empty /jobs page with no job cards shown
5. WHEN a user clicks a popular search term on the Home_Page, THE Search_Handler SHALL populate the hero search input with that term without navigating away, allowing the user to submit the form to execute the search

### Requirement 9: Job Application Flow

**User Story:** As a job seeker, I want to apply to a job from the detail page, so that I can express interest in a position.

#### Acceptance Criteria

1. WHEN a user clicks "Apply Now" on a job detail page, THE Job_Hub SHALL navigate to /jobs/{job}/apply
2. WHEN the application page loads, THE Job_Hub SHALL display the job details including title, company name, salary range, location, description, and required skills
3. IF a user is not authenticated when accessing the application page, THEN THE Job_Hub SHALL redirect the user to the login page and return the user to the application page after successful authentication
4. WHEN an authenticated user accesses the application page, THE Job_Hub SHALL display a confirmation section containing the job title, company name, and salary range, along with a "Confirm Application" button
5. WHEN an authenticated user clicks the "Confirm Application" button, THE Job_Hub SHALL record the application and display a success message indicating the application was submitted
6. IF the job listing does not exist or the job ID is invalid when navigating to the application page, THEN THE Job_Hub SHALL display an error message indicating the job was not found and provide a link back to the job listings page
7. IF an authenticated user has already applied to the same job, THEN THE Job_Hub SHALL display a message indicating a prior application exists and disable the "Confirm Application" button

### Requirement 10: Dynamic Home Page Data

**User Story:** As a developer, I want the home page to load all data from the database, so that content can be managed without code deployments.

#### Acceptance Criteria

1. WHEN the Home_Page loads, THE Job_Hub SHALL query job listings from the job_listings table ordered by created_at descending, limited to 6 records, and pass the results to the view as featured jobs
2. WHEN the Home_Page loads, THE Job_Hub SHALL query companies from the companies table where is_hiring is true, ordered by name ascending, limited to 6 records
3. WHEN the Home_Page loads, THE Job_Hub SHALL query testimonials from the testimonials table where is_featured is true, ordered by created_at descending, limited to 6 records
4. WHEN the Home_Page loads, THE Job_Hub SHALL query career insights from the career_insights table and group results by the type column, returning all records within each group ordered by created_at descending
5. THE Job_Hub SHALL remove all hardcoded static data arrays from the HomeController for job listings, companies, testimonials, and career insights, replacing them with database queries
6. IF a database query for any home page section returns zero records, THEN THE Job_Hub SHALL pass an empty collection to the view so that the corresponding section renders with no items and without errors

### Requirement 11: Company Logo Resolution

**User Story:** As a job seeker, I want company logos to display correctly, so that the platform looks professional and trustworthy.

#### Acceptance Criteria

1. WHEN a company has a non-null and non-empty logo_url stored in the database, THE Job_Hub SHALL display that logo image rendered at 48×48 pixels with object-fit contain
2. IF a company logo_url is null or empty, THEN THE Job_Hub SHALL generate a placeholder image using the UI Avatars service with the first letter of each word in the company name (up to 2 initials) and a background color deterministically derived from the company name so the same company always produces the same color
3. WHEN a job listing references a company, THE Job_Hub SHALL use the company logo_url from the associated companies record
4. IF a company logo image fails to load in the browser (network error or non-image response), THEN THE Job_Hub SHALL display the same UI Avatars placeholder described in criterion 2 without requiring a page reload

### Requirement 12: Database Seeders

**User Story:** As a developer, I want database seeders for all new tables, so that the application has sample data for development and testing.

#### Acceptance Criteria

1. THE Job_Hub SHALL include a CompanySeeder that creates at least 6 sample companies, each with all required fields populated: name, logoUrl, culture (non-empty string), metrics (array with at least 2 entries), perks (array with at least 2 entries), and isHiring (boolean)
2. THE Job_Hub SHALL include a TestimonialSeeder that creates at least 6 sample testimonials, each with all required fields populated: name, role, avatarUrl, text (non-empty string of at most 200 characters), and rating (integer from 1 to 5)
3. THE Job_Hub SHALL include a CareerInsightSeeder that creates at least 4 salary data records, at least 6 hiring trend records, and at least 5 in-demand skills records
4. WHEN seeders run, THE Job_Hub SHALL associate existing job listings with seeded companies via the company_id foreign key, ensuring each job listing references a valid company record
5. THE Job_Hub SHALL register CompanySeeder, TestimonialSeeder, and CareerInsightSeeder in the DatabaseSeeder class so that running `php artisan db:seed` executes all seeders without errors
