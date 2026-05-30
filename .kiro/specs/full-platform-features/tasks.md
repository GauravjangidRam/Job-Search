# Implementation Plan: Full Platform Features

## Overview

This implementation plan covers the comprehensive enhancement of the Job Hub Laravel application to support a multi-role platform with Seeker, Employer, and Admin experiences. Tasks are organized to build foundational infrastructure first (database, models, middleware), then layer on feature-specific controllers and views, and finally wire everything together with notifications and search enhancements.

## Tasks

- [x] 1. Database migrations and model foundations
  - [x] 1.1 Create migration to add role, phone, bio, avatar_path, and company_id columns to users table
    - Add `role` enum column (seeker, employer, admin) with default 'seeker'
    - Add `phone` (nullable varchar), `bio` (nullable text), `avatar_path` (nullable varchar)
    - Add `company_id` (nullable unsigned bigint) with foreign key to companies table (SET NULL on delete)
    - Add index on `role` column
    - _Requirements: 1.1, 2.1, 10.5_

  - [x] 1.2 Create migration to add status column to job_listings table
    - Add `status` varchar column with default 'active'
    - Add index on `status` column
    - _Requirements: 15.1_

  - [x] 1.3 Create migration to add application detail columns to job_applications table
    - Add `applicant_name`, `applicant_email`, `applicant_phone`, `resume_path`, `cover_letter`, `additional_info`, `status`, `status_updated_at` columns
    - Set `status` default to 'applied'
    - Add index on `status` column
    - _Requirements: 4.7, 14.1, 14.2, 14.5_

  - [x] 1.4 Create migration for bookmarks table
    - Create `bookmarks` table with `user_id`, `job_listing_id`, timestamps
    - Add foreign keys with CASCADE on delete
    - Add unique constraint on (user_id, job_listing_id)
    - _Requirements: 13.1, 13.5_

  - [x] 1.5 Update User model with new fillable fields, relationships, and role helpers
    - Add `role`, `phone`, `bio`, `avatar_path`, `company_id` to fillable
    - Add `applications()`, `bookmarks()`, `company()` relationships
    - Add `isSeeker()`, `isEmployer()`, `isAdmin()` helper methods
    - _Requirements: 1.1, 2.1, 10.5_

  - [x] 1.6 Update JobListing model with status, scopes, and relationships
    - Add `status` to fillable
    - Add `scopeActive($query)` scope filtering to status = 'active'
    - Add `applications()` and `bookmarks()` relationships
    - _Requirements: 15.1, 15.2_

  - [x] 1.7 Update JobApplication model with new columns and casts
    - Add `applicant_name`, `applicant_email`, `applicant_phone`, `resume_path`, `cover_letter`, `additional_info`, `status`, `status_updated_at` to fillable
    - Add `status_updated_at` datetime cast
    - _Requirements: 4.7, 14.5_

  - [x] 1.8 Create Bookmark model with relationships
    - Create `app/Models/Bookmark.php`
    - Define `user()` and `jobListing()` belongsTo relationships
    - _Requirements: 13.1_

  - [x] 1.9 Update Company model with employer relationship
    - Add `employers()` hasMany relationship (users where role = employer)
    - _Requirements: 10.5_

- [x] 2. Middleware and authentication enhancements
  - [x] 2.1 Create EnsureRole middleware
    - Create `app/Http/Middleware/EnsureRole.php`
    - Accept role parameter(s), check authenticated user's role
    - Return 403 Forbidden if role does not match
    - Register middleware alias in bootstrap/app.php
    - _Requirements: 1.4, 1.5_

  - [x]* 2.2 Write property test for role-based access control enforcement
    - **Property 1: Role-based access control enforcement**
    - **Validates: Requirements 1.4, 1.5, 9.5**

  - [x] 2.3 Enhance AuthController for intended URL preservation
    - Store intended URL in session when redirecting to login
    - After OTP verification, redirect to stored intended URL or default (/jobs)
    - Preserve intended URL through the entire OTP flow
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

  - [x]* 2.4 Write property test for intended URL preservation round-trip
    - **Property 4: Intended URL preservation round-trip**
    - **Validates: Requirements 3.1, 3.2**

  - [x] 2.5 Enhance RedirectIfAuthenticated middleware for role-based redirects
    - Redirect authenticated seekers to /profile, employers to /employer/dashboard, admins to /admin/dashboard
    - _Requirements: 1.4_

- [ ] 3. Checkpoint - Ensure migrations and middleware work
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. File upload and notification services
  - [x] 4.1 Create FileUploadService
    - Create `app/Services/FileUploadService.php`
    - Implement `uploadResume($file, $userId)` method storing to `storage/app/private/resumes/`
    - Implement `uploadAvatar($file, $userId)` method storing to `storage/app/private/avatars/`
    - Generate unique filenames using `{user_id}_{timestamp}_{original_name}` pattern
    - _Requirements: 2.3, 4.3, 4.4_

  - [x]* 4.2 Write property test for file upload validation
    - **Property 3: File upload validation**
    - **Validates: Requirements 2.3, 4.3**

  - [x]* 4.3 Write property test for unique filename generation
    - **Property 6: Unique filename generation**
    - **Validates: Requirements 4.4**

  - [x] 4.4 Create ApplicationNotificationService
    - Create `app/Services/ApplicationNotificationService.php`
    - Implement `notifyEmployer($application)` method
    - Look up employer via job listing → company → employers relationship
    - Skip silently if no employer found
    - _Requirements: 16.1, 16.4_

  - [x] 4.5 Create ApplicationReceivedMail mailable
    - Create `app/Mail/ApplicationReceivedMail.php`
    - Include applicant name, job title, and link to view application
    - Create Blade email template at `resources/views/emails/application-received.blade.php`
    - _Requirements: 16.2, 16.3_

- [x] 5. Employer registration and company profile
  - [x] 5.1 Create EmployerRegisterRequest form request
    - Validate user fields (name, email, password) and company fields (company_name, industry, description)
    - _Requirements: 10.1_

  - [x] 5.2 Enhance AuthController with employer registration flow
    - Add `showEmployerRegister()` and `employerRegister()` methods
    - Create user with role 'employer', create Company record, link via company_id
    - Create Blade view at `resources/views/auth/employer-register.blade.php`
    - _Requirements: 1.3, 1.6, 10.1, 10.2_

  - [x]* 5.3 Write property test for employer registration creates linked records
    - **Property 14: Employer registration creates linked records**
    - **Validates: Requirements 10.2**

  - [x] 5.4 Create Employer CompanyController
    - Create `app/Http/Controllers/Employer/CompanyController.php`
    - Implement `edit()` and `update()` methods for company profile
    - Create CompanyUpdateRequest form request
    - Create Blade views for company profile edit
    - _Requirements: 10.3, 10.4_

- [x] 6. User profile and bookmarks
  - [x] 6.1 Create ProfileController
    - Create `app/Http/Controllers/ProfileController.php`
    - Implement `show()` displaying user details, applications list, and bookmarks
    - Implement `edit()` and `update()` for profile editing
    - Use FileUploadService for avatar uploads
    - Create ProfileUpdateRequest form request
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

  - [x]* 6.2 Write property test for profile update round-trip
    - **Property 2: Profile update round-trip**
    - **Validates: Requirements 2.2**

  - [x] 6.3 Create profile Blade views
    - Create `resources/views/profile/show.blade.php` with user details, applications list, bookmarks section
    - Create `resources/views/profile/edit.blade.php` with edit form
    - Display applications with job title, company, date, status badge
    - Show empty state with link to browse jobs when no applications exist
    - _Requirements: 2.1, 5.1, 5.2, 5.3, 5.4, 5.5, 14.4_

  - [x] 6.4 Create BookmarkController
    - Create `app/Http/Controllers/BookmarkController.php`
    - Implement `toggle()` method to add/remove bookmark
    - Implement `index()` method to list bookmarked jobs
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

  - [x]* 6.5 Write property test for bookmark toggle idempotence
    - **Property 19: Bookmark toggle idempotence**
    - **Validates: Requirements 13.1, 13.2**

- [ ] 7. Checkpoint - Ensure profile and bookmark features work
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Job application form
  - [x] 8.1 Create JobApplicationRequest form request
    - Validate applicant_name, applicant_email, applicant_phone, resume (pdf/doc/docx, max 5MB), cover_letter, additional_info
    - _Requirements: 4.1, 4.3_

  - [x] 8.2 Enhance JobController with application submission
    - Add `showApplyForm($jobListing)` method pre-filling name/email from auth user
    - Add `submitApplication($jobListing)` method handling file upload and storage
    - Check for duplicate applications before creating
    - Trigger ApplicationNotificationService after successful submission
    - _Requirements: 4.1, 4.2, 4.5, 4.6, 16.1_

  - [x]* 8.3 Write property test for application submission persistence
    - **Property 5: Application submission persistence**
    - **Validates: Requirements 4.2**

  - [x]* 8.4 Write property test for duplicate application prevention
    - **Property 7: Duplicate application prevention**
    - **Validates: Requirements 4.5**

  - [x]* 8.5 Write property test for new application defaults to "applied" status
    - **Property 20: New application defaults to "applied" status**
    - **Validates: Requirements 14.2**

  - [x] 8.6 Create job application Blade views
    - Create `resources/views/jobs/apply.blade.php` with full application form
    - Include file upload field with validation feedback
    - Show duplicate application message if already applied
    - _Requirements: 4.1, 4.5_

- [x] 9. Employer portal - Job management
  - [x] 9.1 Create Employer JobListingController
    - Create `app/Http/Controllers/Employer/JobListingController.php`
    - Implement CRUD: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`
    - Scope all queries to authenticated employer's company
    - Set initial status to 'draft' on creation
    - Create StoreJobListingRequest and UpdateJobListingRequest form requests
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6, 11.7_

  - [x]* 9.2 Write property test for new job listing defaults to draft
    - **Property 15: New job listing defaults to draft**
    - **Validates: Requirements 11.2, 15.3**

  - [x]* 9.3 Write property test for employer job scoping
    - **Property 16: Employer job scoping**
    - **Validates: Requirements 11.3, 11.7**

  - [x] 9.4 Create employer job listing Blade views
    - Create `resources/views/employer/jobs/index.blade.php` listing employer's jobs
    - Create `resources/views/employer/jobs/create.blade.php` with job creation form
    - Create `resources/views/employer/jobs/edit.blade.php` with job edit form
    - _Requirements: 11.1, 11.4_

- [x] 10. Employer portal - Application management and dashboard
  - [x] 10.1 Create Employer ApplicationController
    - Create `app/Http/Controllers/Employer/ApplicationController.php`
    - Implement `index()` with filtering by job listing and status
    - Implement `show()` with full application details and resume download
    - Implement `updateStatus()` to change application status with timestamp
    - Scope all queries to employer's company's job listings
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

  - [x]* 10.2 Write property test for employer application scoping
    - **Property 17: Employer application scoping**
    - **Validates: Requirements 12.1**

  - [x]* 10.3 Write property test for application filter correctness
    - **Property 18: Application filter correctness**
    - **Validates: Requirements 12.5**

  - [x]* 10.4 Write property test for status update records timestamp
    - **Property 21: Status update records timestamp**
    - **Validates: Requirements 14.3**

  - [x] 10.5 Create Employer DashboardController
    - Create `app/Http/Controllers/Employer/DashboardController.php`
    - Display total active listings, total applications, applications by status, recent 5 applications
    - _Requirements: 17.1, 17.2, 17.3, 17.4_

  - [x] 10.6 Create employer application and dashboard Blade views
    - Create `resources/views/employer/applications/index.blade.php` with filters
    - Create `resources/views/employer/applications/show.blade.php` with details
    - Create `resources/views/employer/dashboard.blade.php` with statistics
    - _Requirements: 12.2, 12.3, 12.5, 12.6, 17.1, 17.2, 17.3, 17.4_

- [ ] 11. Checkpoint - Ensure employer portal works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 12. Admin panel - Dashboard and user management
  - [x] 12.1 Create Admin DashboardController
    - Create `app/Http/Controllers/Admin/DashboardController.php`
    - Display total users, total listings, total applications, total companies
    - Display breakdowns by user role, listing status, application status
    - _Requirements: 9.1, 9.2_

  - [x]* 12.2 Write property test for statistics consistency
    - **Property 13: Statistics consistency**
    - **Validates: Requirements 9.1, 9.2**

  - [x] 12.3 Create Admin UserController
    - Create `app/Http/Controllers/Admin/UserController.php`
    - Implement `index()` with pagination, search by name/email, filter by role
    - Implement `updateRole()` with self-change prevention
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x]* 12.4 Write property test for admin user search correctness
    - **Property 9: Admin user search correctness**
    - **Validates: Requirements 6.3**

  - [x]* 12.5 Write property test for admin user filter by role
    - **Property 10: Admin user filter by role**
    - **Validates: Requirements 6.4**

  - [x] 12.6 Create admin dashboard and user management Blade views
    - Create `resources/views/admin/dashboard.blade.php` with statistics cards
    - Create `resources/views/admin/users/index.blade.php` with search, filter, role change
    - _Requirements: 9.1, 9.2, 6.1, 6.3, 6.4_

- [x] 13. Admin panel - Job listings and applications management
  - [x] 13.1 Create Admin JobListingController
    - Create `app/Http/Controllers/Admin/JobListingController.php`
    - Implement `index()` with pagination and status filter
    - Implement `approve()`, `reject()`, `destroy()` actions
    - Cascade delete associated applications on listing delete
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

  - [x]* 13.2 Write property test for listing approval state transition
    - **Property 11: Listing approval state transition**
    - **Validates: Requirements 7.2, 15.4**

  - [x]* 13.3 Write property test for listing status filter correctness
    - **Property 12: Listing status filter correctness**
    - **Validates: Requirements 7.5**

  - [x] 13.4 Create Admin ApplicationController
    - Create `app/Http/Controllers/Admin/ApplicationController.php`
    - Implement `index()` with pagination
    - Implement `updateStatus()` for status changes
    - _Requirements: 8.2, 8.4_

  - [x] 13.5 Create Admin CompanyController
    - Create `app/Http/Controllers/Admin/CompanyController.php`
    - Implement `index()` with pagination showing company name, industry, job count, employer
    - Implement `destroy()` with cascade to job listings
    - _Requirements: 8.1, 8.3_

  - [x] 13.6 Create admin job listings, applications, and companies Blade views
    - Create `resources/views/admin/jobs/index.blade.php` with status filter and actions
    - Create `resources/views/admin/applications/index.blade.php` with status update
    - Create `resources/views/admin/companies/index.blade.php` with delete action
    - _Requirements: 7.1, 7.5, 8.1, 8.2_

- [x] 14. Admin panel - Content management (Testimonials and Career Insights)
  - [x] 14.1 Create Admin TestimonialController
    - Create `app/Http/Controllers/Admin/TestimonialController.php`
    - Implement full CRUD: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`
    - _Requirements: 9.3_

  - [x] 14.2 Create Admin CareerInsightController
    - Create `app/Http/Controllers/Admin/CareerInsightController.php`
    - Implement full CRUD: `index()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`
    - _Requirements: 9.4_

  - [x] 14.3 Create admin testimonial and career insight Blade views
    - Create CRUD views for testimonials (index, create, edit)
    - Create CRUD views for career insights (index, create, edit)
    - _Requirements: 9.3, 9.4_

- [ ] 15. Checkpoint - Ensure admin panel works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 16. Enhanced search filters and public job listing status enforcement
  - [x] 16.1 Enhance JobController with search filters and status enforcement
    - Add filters for job_type, location_type, salary_range, company_name
    - Apply `scopeActive()` to all public queries (only show active listings)
    - Combine filters with AND logic
    - Preserve filters across pagination
    - Perform case-insensitive partial match for company_name filter
    - _Requirements: 15.2, 15.5, 18.1, 18.2, 18.3, 18.4, 18.5_

  - [x]* 16.2 Write property test for only active listings visible in public search
    - **Property 22: Only active listings visible in public search**
    - **Validates: Requirements 15.2, 15.5**

  - [x]* 16.3 Write property test for search filters combine with AND logic
    - **Property 23: Search filters combine with AND logic**
    - **Validates: Requirements 18.2, 18.5**

  - [x] 16.4 Update job listing Blade views with filter UI
    - Add filter dropdowns/inputs for job_type, location_type, salary_range, company_name
    - Use Alpine.js for interactive filter toggling
    - Show bookmark button state for authenticated seekers
    - _Requirements: 18.1, 13.4_

- [ ] 17. Route registration and wiring
  - [ ] 17.1 Register all routes in web.php with proper middleware groups
    - Public routes: home, jobs, companies, insights
    - Guest routes: login, register, employer-register, OTP
    - Seeker routes (auth + role:seeker): profile, bookmarks, job applications
    - Employer routes (auth + role:employer): dashboard, jobs, applications, company
    - Admin routes (auth + role:admin): dashboard, users, jobs, companies, applications, testimonials, insights
    - _Requirements: 1.4, 9.5_

  - [ ] 17.2 Add navigation links based on user role
    - Update layout/navigation Blade components to show role-appropriate links
    - Show profile/bookmarks for seekers, employer portal for employers, admin panel for admins
    - _Requirements: 1.4_

- [ ] 18. Database seeder updates
  - [ ] 18.1 Update DatabaseSeeder with role-based test users and sample data
    - Create admin user, employer user with company, seeker users
    - Create sample job listings with various statuses
    - Create sample applications with various statuses
    - Create sample bookmarks
    - _Requirements: 1.1, 1.2, 1.3_

- [ ] 19. Final checkpoint - Ensure all features work end-to-end
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 20. Write integration tests for application ordering
  - [ ]* 20.1 Write property test for application ordering by date
    - **Property 8: Application ordering by date**
    - **Validates: Requirements 5.4**

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- The tech stack is Laravel 13, Blade templates, Alpine.js, Tailwind CSS, SQLite
- All file uploads use Laravel's Storage facade with the local disk
- No paid third-party packages are introduced

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3", "1.4"] },
    { "id": 1, "tasks": ["1.5", "1.6", "1.7", "1.8", "1.9"] },
    { "id": 2, "tasks": ["2.1", "2.3", "2.5", "4.1", "4.4", "4.5"] },
    { "id": 3, "tasks": ["2.2", "2.4", "4.2", "4.3", "5.1", "5.4"] },
    { "id": 4, "tasks": ["5.2", "5.3", "6.1", "6.4"] },
    { "id": 5, "tasks": ["6.2", "6.3", "6.5", "8.1"] },
    { "id": 6, "tasks": ["8.2", "8.6"] },
    { "id": 7, "tasks": ["8.3", "8.4", "8.5", "9.1"] },
    { "id": 8, "tasks": ["9.2", "9.3", "9.4"] },
    { "id": 9, "tasks": ["10.1", "10.5"] },
    { "id": 10, "tasks": ["10.2", "10.3", "10.4", "10.6"] },
    { "id": 11, "tasks": ["12.1", "12.3"] },
    { "id": 12, "tasks": ["12.2", "12.4", "12.5", "12.6"] },
    { "id": 13, "tasks": ["13.1", "13.4", "13.5"] },
    { "id": 14, "tasks": ["13.2", "13.3", "13.6", "14.1", "14.2"] },
    { "id": 15, "tasks": ["14.3", "16.1"] },
    { "id": 16, "tasks": ["16.2", "16.3", "16.4"] },
    { "id": 17, "tasks": ["17.1", "17.2", "18.1"] },
    { "id": 18, "tasks": ["20.1"] }
  ]
}
```
