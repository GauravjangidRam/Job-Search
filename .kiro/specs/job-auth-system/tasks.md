# Implementation Plan: Job Auth System

## Overview

This plan implements job listing/detail pages, user authentication with email OTP verification, MySQL database integration, and navigation updates for the Job Hub Laravel application. Tasks are ordered to build foundational database and model layers first, then controllers and views, then authentication flows, and finally integration and wiring.

## Tasks

- [x] 1. Database setup and migrations
  - [x] 1.1 Update database configuration for MySQL
    - Update `.env.example` with MySQL connection variables (DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
    - Update `config/database.php` default connection to use env variable
    - _Requirements: 6.1_

  - [x] 1.2 Create job_listings migration
    - Create migration with columns: title (string 255), company_name (string 255), company_logo_url (string 2048 nullable), location (string 255), salary_min (unsigned integer), salary_max (unsigned integer), job_type (string 50), location_type (string 50), description (text), skills (json), timestamps
    - Add index on `job_type` column and index on `location_type` column
    - _Requirements: 6.3, 6.6_

  - [x] 1.3 Create users table OTP migration
    - Create a migration to add `otp` (string 255, nullable), `otp_expires_at` (timestamp, nullable), and `otp_attempts` (unsigned tinyint, default 0) columns to the users table
    - _Requirements: 6.4_

- [x] 2. Eloquent models
  - [x] 2.1 Create JobListing model
    - Create `app/Models/JobListing.php` with fillable fields: title, company_name, company_logo_url, location, salary_min, salary_max, job_type, location_type, description, skills
    - Define casts: skills as array, salary_min as integer, salary_max as integer
    - _Requirements: 6.3_

  - [x] 2.2 Update User model for OTP fields
    - Add otp, otp_expires_at, otp_attempts to fillable array
    - Add otp to hidden array
    - Add otp_expires_at cast as datetime
    - Ensure password cast as hashed is present
    - _Requirements: 6.4, 3.7_

- [x] 3. Implement OTP service
  - [x] 3.1 Create OtpService class
    - Create `app/Services/OtpService.php` with methods: generate(User), verify(User, string), invalidate(User), isExpired(User)
    - generate() creates a random 6-digit numeric OTP, stores hashed value via Hash::make(), sets otp_expires_at to now + 10 minutes, resets otp_attempts to 0
    - verify() uses Hash::check() to compare submitted OTP against stored hash, increments otp_attempts on failure
    - invalidate() clears otp, otp_expires_at, and otp_attempts fields
    - isExpired() checks if otp_expires_at is in the past
    - _Requirements: 4.1, 4.2, 4.4, 4.5, 4.6, 4.7_

  - [ ]* 3.2 Write property test for OTP generation invariants
    - **Property 4: OTP generation invariants**
    - Verify generated OTP is exactly 6 digits, stored value is hashed (not equal to plain text), and otp_expires_at is ~10 minutes in the future
    - Use data provider with 100+ random iterations
    - **Validates: Requirements 4.1, 4.2**

  - [ ]* 3.3 Write property test for OTP regeneration invalidation
    - **Property 5: OTP regeneration invalidates previous**
    - Verify that after generating a new OTP, the previous OTP fails verification and only the new one passes
    - Use data provider with 100+ random iterations
    - **Validates: Requirements 4.7**

- [x] 4. Form request validation classes
  - [x] 4.1 Create RegisterRequest form request
    - Create `app/Http/Requests/RegisterRequest.php`
    - Validate: name (required, string, max:255), email (required, email, max:255, unique:users), password (required, string, min:8, max:72)
    - Define custom error messages for each validation rule
    - _Requirements: 3.2, 3.3, 3.4, 3.5, 3.6_

  - [x] 4.2 Create LoginRequest form request
    - Create `app/Http/Requests/LoginRequest.php`
    - Validate: email (required, email), password (required, string)
    - _Requirements: 5.1, 5.3_

  - [x] 4.3 Create OtpVerifyRequest form request
    - Create `app/Http/Requests/OtpVerifyRequest.php`
    - Validate: otp (required, string, size:6, regex:/^\d{6}$/)
    - _Requirements: 4.3, 4.4_

  - [ ]* 4.4 Write property test for password length validation boundary
    - **Property 2: Password length validation boundary**
    - Verify strings < 8 or > 72 chars are rejected, strings 8-72 chars are accepted
    - Use data provider with 100+ random iterations
    - **Validates: Requirements 3.4**

- [x] 5. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement AuthController
  - [x] 6.1 Create AuthController with registration methods
    - Create `app/Http/Controllers/AuthController.php`
    - Implement showRegister() returning the registration view
    - Implement register() using RegisterRequest, creating user with hashed password, calling OtpService::generate(), sending OTP email via Laravel Mail, redirecting to OTP verification page
    - _Requirements: 3.1, 3.2, 3.7, 4.1_

  - [x] 6.2 Implement login methods on AuthController
    - Implement showLogin() returning the login view, redirecting to /jobs if already authenticated
    - Implement login() using LoginRequest, checking rate limiter (5 attempts per 15 min per email), authenticating via Auth::attempt(), redirecting unverified users to OTP verification with new OTP, redirecting verified users to /jobs
    - Use generic error message for failed credentials regardless of whether email exists
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.8_

  - [x] 6.3 Implement OTP verification methods on AuthController
    - Implement showOtpVerification() returning the OTP verification view
    - Implement verifyOtp() using OtpVerifyRequest, checking expiration, checking attempts (max 5), verifying OTP, marking email as verified, logging user in, redirecting to /jobs
    - Implement resendOtp() with rate limiting (3 per 15 min per email), invalidating old OTP, generating new one, sending email
    - _Requirements: 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9_

  - [x] 6.4 Implement logout method on AuthController
    - Implement logout() invalidating session and redirecting to home page
    - _Requirements: 5.7_

  - [ ]* 6.5 Write property test for login error message uniformity
    - **Property 6: Login error message uniformity**
    - Verify that login failures return the same generic error message regardless of whether the email exists in the database
    - Use data provider with 100+ random iterations using random emails (some existing, some not)
    - **Validates: Requirements 5.4**

  - [ ]* 6.6 Write property test for password storage hashing
    - **Property 3: Password storage hashing**
    - Verify stored password never equals plain text and Hash::check(plain, stored) returns true
    - Use data provider with 100+ random iterations
    - **Validates: Requirements 3.7**

- [x] 7. Implement JobController
  - [x] 7.1 Create JobController with index method
    - Create `app/Http/Controllers/JobController.php`
    - Implement index() accepting filter parameters (job_type, location_type, salary_min, salary_max, search), building query with conditions, ordering by created_at descending, paginating at 12 per page
    - Handle page overflow by redirecting to last available page
    - _Requirements: 1.1, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8_

  - [x] 7.2 Implement show method on JobController
    - Implement show() using route model binding with `JobListing` model
    - Add route constraint for numeric ID to return 404 for non-numeric IDs
    - _Requirements: 2.1, 2.3, 2.5_

  - [ ]* 7.3 Write property test for job filtering and search
    - **Property 1: Job filtering and search returns only matching results**
    - Verify that for any combination of filters and search terms, all returned jobs match all criteria and results are ordered by posted date descending
    - Use data provider with 100+ random iterations generating random filter combinations
    - **Validates: Requirements 1.1, 1.3, 1.4, 1.7**

- [x] 8. Define routes
  - [x] 8.1 Add job and auth routes to web.php
    - Add GET /jobs → JobController@index
    - Add GET /jobs/{job} → JobController@show (with numeric constraint)
    - Add GET /register → AuthController@showRegister (guest middleware)
    - Add POST /register → AuthController@register (guest middleware)
    - Add GET /login → AuthController@showLogin (guest middleware)
    - Add POST /login → AuthController@login (guest middleware, throttle)
    - Add GET /verify-otp → AuthController@showOtpVerification
    - Add POST /verify-otp → AuthController@verifyOtp
    - Add POST /resend-otp → AuthController@resendOtp (throttle)
    - Add POST /logout → AuthController@logout (auth middleware)
    - _Requirements: 1.1, 2.1, 3.1, 5.1, 5.7_

- [x] 9. Checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 10. Implement views - Job pages
  - [x] 10.1 Create job listing page view
    - Create `resources/views/jobs/index.blade.php` extending `layouts.app`
    - Display filter controls (job type dropdown, location type dropdown, salary range inputs) using Alpine.js for interactivity
    - Display search input field with form submission
    - Display paginated job cards showing title, company name, location, salary range, job type, posted date
    - Display pagination controls with current page, total pages, next/previous links
    - Display "No jobs found" message when results are empty
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

  - [x] 10.2 Create job detail page view
    - Create `resources/views/jobs/show.blade.php` extending `layouts.app`
    - Display job title, company name, company logo (with placeholder fallback), location, salary range, job type, location type, full description, skills list, and posted date as relative time (e.g., "3 days ago")
    - Include "Back to Jobs" link navigating to /jobs
    - _Requirements: 2.2, 2.4, 2.6_

  - [x] 10.3 Create 404 error page for jobs
    - Create or update `resources/views/errors/404.blade.php` with "Job not found" message and link back to /jobs
    - _Requirements: 2.3, 2.5_

- [x] 11. Implement views - Auth pages
  - [x] 11.1 Create registration form view
    - Create `resources/views/auth/register.blade.php` extending `layouts.app`
    - Include form fields for name, email, password with inline validation error display
    - Preserve old input (except password) on validation failure
    - _Requirements: 3.1, 3.5_

  - [x] 11.2 Create login form view
    - Create `resources/views/auth/login.blade.php` extending `layouts.app`
    - Include form fields for email and password with inline validation error display
    - Display rate limit error message when account is temporarily locked
    - _Requirements: 5.1, 5.6_

  - [x] 11.3 Create OTP verification view
    - Create `resources/views/auth/verify-otp.blade.php` extending `layouts.app`
    - Include OTP input field (6 digits), submit button, and resend OTP link
    - Display error messages for invalid, expired, or locked OTP states
    - Display throttle message when resend limit is reached
    - _Requirements: 4.3, 4.4, 4.5, 4.6, 4.8, 4.9_

- [x] 12. Implement views - Error and navigation
  - [x] 12.1 Create 503 service unavailable error page
    - Create `resources/views/errors/503.blade.php` with generic "Service temporarily unavailable" message
    - Ensure no database credentials, host, port, or driver details are exposed
    - _Requirements: 6.5_

  - [ ]* 12.2 Write property test for error page credential concealment
    - **Property 7: Error page credential concealment**
    - Verify that for any database connection failure, the rendered error page does not contain DB host, port, username, password, or driver error details
    - Use data provider with 100+ random iterations using random credential values
    - **Validates: Requirements 6.5**

  - [x] 12.3 Update navigation bar component
    - Update `resources/views/components/home/navigation-bar.blade.php`
    - Show "Login" link (/login) and "Register" link (/register) when user is guest
    - Show user name (truncated to 20 characters) and "Logout" form (POST /logout with CSRF) when user is authenticated
    - Update "Jobs" link to navigate to /jobs
    - Ensure mobile menu toggle works for viewport < 768px using Alpine.js
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6_

  - [ ]* 12.4 Write property test for navigation name truncation
    - **Property 8: Navigation name truncation**
    - Verify that names > 20 characters are displayed truncated to 20 characters, and names ≤ 20 characters are displayed in full
    - Use data provider with 100+ random iterations generating random-length names
    - **Validates: Requirements 7.2**

- [x] 13. Exception handling and database error safety
  - [x] 13.1 Configure exception handler for database errors
    - Update Laravel exception handler to catch `QueryException` and `PDOException`
    - Render `errors/503.blade.php` for database connection failures
    - Ensure no sensitive database information is leaked in any error response
    - _Requirements: 6.2, 6.5_

- [x] 14. Email integration for OTP
  - [x] 14.1 Create OTP mail class and template
    - Create `app/Mail/OtpMail.php` Mailable class accepting the plain-text OTP
    - Create `resources/views/emails/otp.blade.php` email template displaying the 6-digit OTP with instructions
    - Wire OTP email sending in AuthController registration and login (unverified user) flows
    - _Requirements: 4.1_

- [x] 15. Database seeder for job listings
  - [x] 15.1 Create JobListing seeder with sample data
    - Create `database/seeders/JobListingSeeder.php` with at least 20 sample job listings covering various job types, location types, and salary ranges
    - Update `DatabaseSeeder.php` to call JobListingSeeder
    - _Requirements: 1.1, 6.3_

- [x] 16. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- The project uses PHP/Laravel with Blade views, Tailwind CSS, and Alpine.js
- OTP is stored hashed (255 chars) not as plain text (6 chars)
- Rate limiting uses Laravel's built-in RateLimiter facade

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3"] },
    { "id": 1, "tasks": ["2.1", "2.2"] },
    { "id": 2, "tasks": ["3.1", "4.1", "4.2", "4.3"] },
    { "id": 3, "tasks": ["3.2", "3.3", "4.4", "6.1"] },
    { "id": 4, "tasks": ["6.2", "6.3", "6.4", "7.1", "7.2"] },
    { "id": 5, "tasks": ["6.5", "6.6", "7.3", "8.1"] },
    { "id": 6, "tasks": ["10.1", "10.2", "10.3", "11.1", "11.2", "11.3"] },
    { "id": 7, "tasks": ["12.1", "12.3", "13.1", "14.1"] },
    { "id": 8, "tasks": ["12.2", "12.4", "15.1"] }
  ]
}
```
