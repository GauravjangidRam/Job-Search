# Requirements Document

## Introduction

This document specifies the requirements for a comprehensive platform enhancement to the Job Hub application. The enhancement introduces user profiles, a proper job application form with file uploads, login redirect preservation, an admin panel for platform management, an employer portal for job posting and applicant management, and additional features such as job bookmarks, email notifications, and application status tracking. All features are built from scratch using Laravel, Blade, Alpine.js, and Tailwind CSS without paid third-party packages.

## Glossary

- **Platform**: The Job Hub Laravel web application
- **Seeker**: A registered user with the role "seeker" who searches and applies for jobs
- **Employer**: A registered user with the role "employer" who posts jobs and manages applications on behalf of a company
- **Admin**: A registered user with the role "admin" who manages all platform content and users
- **Profile_Page**: The authenticated user's personal dashboard showing their details and activity
- **Application_Form**: The multi-field form a Seeker submits when applying to a job listing
- **Admin_Panel**: The administrative dashboard accessible only to Admin users
- **Employer_Portal**: The employer-facing dashboard for managing company profiles, job postings, and received applications
- **Job_Listing**: A job posting record in the job_listings table
- **Job_Application**: A record in the job_applications table representing a Seeker's application to a Job_Listing
- **Company_Profile**: A company record in the companies table, linked to an Employer
- **Bookmark**: A saved reference from a Seeker to a Job_Listing for later review
- **Application_Status**: The current state of a Job_Application (applied, reviewed, shortlisted, rejected)
- **Listing_Status**: The current state of a Job_Listing (draft, active, closed)
- **Intended_URL**: The URL a user was attempting to access before being redirected to login
- **Auth_Middleware**: Laravel middleware that restricts access to authenticated users and redirects unauthenticated users to the login page

## Requirements

### Requirement 1: Role-Based User System

**User Story:** As a platform operator, I want users to have distinct roles (seeker, employer, admin), so that each user type has appropriate access and capabilities.

#### Acceptance Criteria

1. THE Platform SHALL store a role column on the users table with allowed values "seeker", "employer", and "admin"
2. WHEN a new user registers through the standard registration form, THE Platform SHALL assign the "seeker" role by default
3. WHEN a new user registers through the employer registration form, THE Platform SHALL assign the "employer" role
4. THE Platform SHALL enforce role-based access control on all protected routes using middleware
5. IF an authenticated user attempts to access a route restricted to a different role, THEN THE Platform SHALL return a 403 Forbidden response
6. THE Platform SHALL provide a separate registration flow for employers that collects company information during signup

### Requirement 2: User Profile Page

**User Story:** As a Seeker, I want a profile page where I can view and edit my personal details, so that I can keep my information up to date.

#### Acceptance Criteria

1. WHEN an authenticated Seeker navigates to the profile route, THE Profile_Page SHALL display the user's name, email, phone number, bio, and avatar
2. WHEN a Seeker submits the profile edit form with valid data, THE Platform SHALL update the user's name, phone, bio, and avatar in the database
3. WHEN a Seeker uploads an avatar image, THE Platform SHALL validate that the file is a JPEG, PNG, or WebP image with a maximum size of 2 MB
4. IF a Seeker submits the profile edit form with invalid data, THEN THE Platform SHALL display validation error messages next to the corresponding fields
5. THE Profile_Page SHALL not allow modification of the email address field
6. WHEN a Seeker views the profile page, THE Profile_Page SHALL display a section listing all jobs the Seeker has applied to

### Requirement 3: Login Redirect Preservation

**User Story:** As a Seeker, I want to be redirected back to the page I was trying to access after logging in, so that I do not lose my navigation context.

#### Acceptance Criteria

1. WHEN an unauthenticated user attempts to access a protected route, THE Auth_Middleware SHALL store the Intended_URL in the session before redirecting to the login page
2. WHEN a user successfully completes login (including OTP verification), THE Platform SHALL redirect the user to the stored Intended_URL
3. IF no Intended_URL is stored in the session, THEN THE Platform SHALL redirect the user to the default post-login route (/jobs)
4. WHEN a user navigates directly to the login page without an Intended_URL, THE Platform SHALL redirect to the default post-login route after successful authentication
5. THE Platform SHALL preserve the Intended_URL through the entire OTP verification flow (login, OTP entry, verification)

### Requirement 4: Job Application Form

**User Story:** As a Seeker, I want to submit a detailed application with my resume and cover letter, so that employers can evaluate my candidacy properly.

#### Acceptance Criteria

1. WHEN an authenticated Seeker navigates to the job application page, THE Application_Form SHALL display fields for applicant name, email, phone number, resume file upload, cover letter text area, and additional information text area
2. WHEN a Seeker submits the Application_Form with valid data, THE Platform SHALL store the application details including the uploaded resume file in the database
3. WHEN a Seeker uploads a resume file, THE Platform SHALL validate that the file is a PDF, DOC, or DOCX with a maximum size of 5 MB
4. THE Platform SHALL store uploaded resume files in a dedicated storage directory with unique filenames to prevent collisions
5. IF a Seeker has already applied to a specific Job_Listing, THEN THE Platform SHALL prevent duplicate submissions and display a message indicating the existing application
6. WHEN a Seeker submits the Application_Form, THE Platform SHALL pre-fill the name and email fields from the authenticated user's profile data
7. THE Platform SHALL add columns to the job_applications table for applicant_name, applicant_email, applicant_phone, resume_path, cover_letter, additional_info, and status

### Requirement 5: Profile Applications Section

**User Story:** As a Seeker, I want to see all my job applications on my profile page, so that I can track my application history and status.

#### Acceptance Criteria

1. WHEN a Seeker views the profile page, THE Profile_Page SHALL display a list of all Job_Applications submitted by that Seeker
2. THE Profile_Page SHALL display each application's job title, company name, date applied, and current Application_Status
3. WHEN a Seeker clicks on an application entry, THE Profile_Page SHALL display the full application details including cover letter and additional information
4. THE Profile_Page SHALL order applications by date applied in descending order (most recent first)
5. IF a Seeker has no applications, THEN THE Profile_Page SHALL display an empty state message with a link to browse jobs

### Requirement 6: Admin Panel - User Management

**User Story:** As an Admin, I want to view and manage all registered users, so that I can maintain platform integrity.

#### Acceptance Criteria

1. WHEN an Admin navigates to the admin users section, THE Admin_Panel SHALL display a paginated list of all users with their name, email, role, and registration date
2. WHEN an Admin changes a user's role, THE Admin_Panel SHALL update the role in the database and reflect the change immediately
3. THE Admin_Panel SHALL allow Admins to search users by name or email
4. THE Admin_Panel SHALL allow Admins to filter users by role
5. IF an Admin attempts to change their own role, THEN THE Admin_Panel SHALL reject the action and display an error message

### Requirement 7: Admin Panel - Job Listings Management

**User Story:** As an Admin, I want to manage all job listings, so that I can ensure quality and appropriateness of posted content.

#### Acceptance Criteria

1. WHEN an Admin navigates to the admin jobs section, THE Admin_Panel SHALL display a paginated list of all Job_Listings with title, company, status, and creation date
2. WHEN an Admin approves a Job_Listing, THE Admin_Panel SHALL set the Listing_Status to "active" and make the listing visible to Seekers
3. WHEN an Admin rejects a Job_Listing, THE Admin_Panel SHALL set the Listing_Status to "closed" and remove the listing from public search results
4. WHEN an Admin deletes a Job_Listing, THE Admin_Panel SHALL remove the listing and all associated Job_Applications from the database
5. THE Admin_Panel SHALL allow Admins to filter job listings by Listing_Status

### Requirement 8: Admin Panel - Companies and Applications Management

**User Story:** As an Admin, I want to manage companies and applications, so that I can oversee all platform activity.

#### Acceptance Criteria

1. WHEN an Admin navigates to the admin companies section, THE Admin_Panel SHALL display a paginated list of all companies with name, industry, job count, and linked employer
2. WHEN an Admin navigates to the admin applications section, THE Admin_Panel SHALL display a paginated list of all Job_Applications with applicant name, job title, company, status, and date
3. THE Admin_Panel SHALL allow Admins to delete a company and cascade the deletion to associated job listings
4. THE Admin_Panel SHALL allow Admins to update the Application_Status of any Job_Application

### Requirement 9: Admin Panel - Statistics and Content Management

**User Story:** As an Admin, I want to view platform statistics and manage testimonials and career insights, so that I can monitor platform health and curate content.

#### Acceptance Criteria

1. WHEN an Admin navigates to the admin dashboard, THE Admin_Panel SHALL display summary statistics including total users, total job listings, total applications, and total companies
2. THE Admin_Panel SHALL display statistics broken down by user role count, job listing status count, and application status count
3. WHEN an Admin navigates to the testimonials section, THE Admin_Panel SHALL allow creating, editing, and deleting testimonials
4. WHEN an Admin navigates to the career insights section, THE Admin_Panel SHALL allow creating, editing, and deleting career insight entries
5. THE Admin_Panel SHALL be accessible only to users with the "admin" role

### Requirement 10: Employer Registration and Company Profile

**User Story:** As an Employer, I want to register an employer account and create a company profile, so that I can post jobs and manage applications.

#### Acceptance Criteria

1. WHEN a user selects employer registration, THE Platform SHALL display a registration form that collects user details (name, email, password) and company details (company name, industry, description)
2. WHEN an Employer completes registration, THE Platform SHALL create a user record with role "employer" and a linked Company_Profile record
3. WHEN an Employer navigates to the company profile page, THE Employer_Portal SHALL display the company details with an edit form
4. WHEN an Employer updates the Company_Profile, THE Employer_Portal SHALL validate and save the changes including company name, description, industry, website URL, logo, and employee count
5. THE Platform SHALL link the Employer user to exactly one Company_Profile through a company_id foreign key on the users table

### Requirement 11: Employer Job Posting

**User Story:** As an Employer, I want to create and manage job listings, so that I can attract qualified candidates.

#### Acceptance Criteria

1. WHEN an Employer navigates to the job creation page, THE Employer_Portal SHALL display a form with fields for title, description, location, salary range, job type, location type, and required skills
2. WHEN an Employer submits a valid job creation form, THE Platform SHALL create a Job_Listing record linked to the Employer's company with Listing_Status set to "draft"
3. WHEN an Employer views their job listings, THE Employer_Portal SHALL display only jobs belonging to the Employer's company
4. WHEN an Employer edits a Job_Listing, THE Employer_Portal SHALL allow updating all job fields and changing the Listing_Status
5. WHEN an Employer closes a Job_Listing, THE Employer_Portal SHALL set the Listing_Status to "closed" and remove the listing from public search results
6. WHEN an Employer deletes a Job_Listing, THE Employer_Portal SHALL remove the listing and all associated Job_Applications from the database
7. THE Employer_Portal SHALL restrict job management actions to listings belonging to the authenticated Employer's company

### Requirement 12: Employer Application Management

**User Story:** As an Employer, I want to view and manage applications received for my job postings, so that I can evaluate candidates and progress them through the hiring pipeline.

#### Acceptance Criteria

1. WHEN an Employer navigates to the applications section, THE Employer_Portal SHALL display all Job_Applications for jobs belonging to the Employer's company
2. THE Employer_Portal SHALL display each application's applicant name, job title, date applied, and current Application_Status
3. WHEN an Employer clicks on an application, THE Employer_Portal SHALL display the full application details including resume download link, cover letter, and additional information
4. WHEN an Employer updates an Application_Status, THE Employer_Portal SHALL save the new status and display a confirmation message
5. THE Employer_Portal SHALL allow filtering applications by job listing and by Application_Status
6. THE Employer_Portal SHALL display a dashboard with total applications received, applications by status, and recent applications

### Requirement 13: Job Bookmarks

**User Story:** As a Seeker, I want to save job listings for later review, so that I can organize my job search.

#### Acceptance Criteria

1. WHEN an authenticated Seeker clicks the bookmark button on a Job_Listing, THE Platform SHALL save the bookmark to the database
2. WHEN a Seeker clicks the bookmark button on an already-bookmarked Job_Listing, THE Platform SHALL remove the bookmark from the database
3. WHEN a Seeker navigates to the bookmarks section on the Profile_Page, THE Platform SHALL display all bookmarked Job_Listings with title, company, and date saved
4. THE Platform SHALL indicate the bookmarked state on Job_Listing cards and detail pages for authenticated Seekers
5. IF a bookmarked Job_Listing is deleted, THEN THE Platform SHALL remove the associated bookmark records

### Requirement 14: Application Status Tracking

**User Story:** As a Seeker, I want to see the current status of my applications, so that I can understand where I stand in the hiring process.

#### Acceptance Criteria

1. THE Platform SHALL support the following Application_Status values: "applied", "reviewed", "shortlisted", and "rejected"
2. WHEN a Job_Application is created, THE Platform SHALL set the initial Application_Status to "applied"
3. WHEN an Employer or Admin updates an Application_Status, THE Platform SHALL record the timestamp of the status change
4. WHEN a Seeker views their applications on the Profile_Page, THE Platform SHALL display the current Application_Status with a visual indicator (color-coded badge)
5. THE Platform SHALL store a status_updated_at timestamp on the job_applications table

### Requirement 15: Job Listing Status Management

**User Story:** As a platform operator, I want job listings to have lifecycle states, so that only approved and active listings are visible to Seekers.

#### Acceptance Criteria

1. THE Platform SHALL support the following Listing_Status values: "draft", "active", and "closed"
2. WHEN a Seeker browses or searches job listings, THE Platform SHALL display only Job_Listings with Listing_Status "active"
3. WHEN an Employer creates a new Job_Listing, THE Platform SHALL set the initial Listing_Status to "draft"
4. WHEN an Admin approves a Job_Listing, THE Platform SHALL change the Listing_Status from "draft" to "active"
5. WHILE a Job_Listing has Listing_Status "closed", THE Platform SHALL exclude the listing from all public search results and listing pages

### Requirement 16: Email Notifications

**User Story:** As an Employer, I want to receive email notifications when a Seeker applies to my job, so that I can respond to candidates promptly.

#### Acceptance Criteria

1. WHEN a Seeker submits a Job_Application, THE Platform SHALL send an email notification to the Employer associated with the Job_Listing's company
2. THE Platform SHALL include the applicant name, job title, and a link to view the application in the notification email
3. THE Platform SHALL use Laravel's built-in Mail system with a Blade email template for notification formatting
4. IF the Job_Listing has no associated company or employer, THEN THE Platform SHALL skip the email notification without raising an error

### Requirement 17: Employer Dashboard Statistics

**User Story:** As an Employer, I want to see statistics about my job postings and applications, so that I can understand my recruitment activity.

#### Acceptance Criteria

1. WHEN an Employer navigates to the Employer_Portal dashboard, THE Employer_Portal SHALL display the total number of active job listings for the Employer's company
2. THE Employer_Portal SHALL display the total number of applications received across all of the Employer's job listings
3. THE Employer_Portal SHALL display a breakdown of applications by Application_Status
4. THE Employer_Portal SHALL display the most recent 5 applications received with applicant name, job title, and date

### Requirement 18: Enhanced Search Filters

**User Story:** As a Seeker, I want additional search filters, so that I can find relevant jobs more efficiently.

#### Acceptance Criteria

1. WHEN a Seeker uses the job search page, THE Platform SHALL provide filter options for job type, location type, salary range, and company name
2. WHEN a Seeker applies multiple filters simultaneously, THE Platform SHALL combine all active filters using AND logic
3. WHEN a Seeker clears all filters, THE Platform SHALL display all active Job_Listings without any filter constraints
4. THE Platform SHALL preserve applied filters across pagination navigation
5. WHEN a Seeker filters by company name, THE Platform SHALL perform a case-insensitive partial match against the company_name field
