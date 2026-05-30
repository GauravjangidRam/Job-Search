# Requirements Document

## Introduction

This feature adds a job listing/detail page, user authentication (registration and login with email OTP verification), and MySQL database integration to the existing Job Hub Laravel application. The system currently serves a static home page with mock data. This feature introduces persistent data storage, user accounts with passwordless OTP-based email verification, and a dedicated page for browsing and viewing job listings.

## Glossary

- **Job_Listing_Page**: The page that displays all available jobs with filtering and search capabilities
- **Job_Detail_Page**: The page that displays full information about a single job
- **Registration_Form**: The UI form where new users provide their name, email, and password to create an account
- **Login_Form**: The UI form where existing users provide their email and password to authenticate
- **OTP_Service**: The backend service responsible for generating, sending, and validating one-time passwords via email
- **OTP**: A 6-digit numeric one-time password sent to the user's email address, valid for 10 minutes
- **Auth_Controller**: The controller handling registration, login, OTP verification, and logout actions
- **Job_Controller**: The controller responsible for serving job listing and job detail pages
- **User_Model**: The Eloquent model representing a registered user in the database
- **Job_Model**: The Eloquent model representing a job posting stored in the database
- **Database_Connection**: The MySQL database connection configured via Laravel's database configuration
- **Session_Guard**: Laravel's session-based authentication guard used to maintain user login state

## Requirements

### Requirement 1: Job Listing Page

**User Story:** As a job seeker, I want to browse all available job postings on a dedicated page, so that I can discover opportunities beyond the featured jobs on the home page.

#### Acceptance Criteria

1. WHEN a user navigates to `/jobs`, THE Job_Controller SHALL return the Job_Listing_Page with a paginated list of jobs from the database, ordered by posted date descending (newest first)
2. THE Job_Listing_Page SHALL display each job's title, company name, location, salary range, job type, and posted date
3. WHEN a user applies a filter (job type, location type, or salary range), THE Job_Listing_Page SHALL display only jobs matching all currently selected filter criteria and reset pagination to page 1
4. WHEN a user enters a search term of at least 1 character in the search field and submits the search, THE Job_Listing_Page SHALL display only jobs whose title or company name contains the search term using case-insensitive matching
5. WHEN no jobs match the active filters or search term, THE Job_Listing_Page SHALL display a "No jobs found" message
6. THE Job_Listing_Page SHALL display 12 jobs per page with pagination controls showing the current page number, total pages, and next/previous navigation links
7. WHEN a user applies both filters and a search term simultaneously, THE Job_Listing_Page SHALL display only jobs that match all active filters AND contain the search term
8. IF the user navigates to `/jobs` with a page number exceeding the total available pages, THEN THE Job_Listing_Page SHALL redirect to the last available page

### Requirement 2: Job Detail Page

**User Story:** As a job seeker, I want to view the full details of a specific job posting, so that I can determine whether the role is a good fit for me.

#### Acceptance Criteria

1. WHEN a user navigates to `/jobs/{id}` where `{id}` is a valid numeric identifier matching an existing job, THE Job_Controller SHALL return the Job_Detail_Page for the specified job
2. THE Job_Detail_Page SHALL display the job title, company name, company logo (if available), location, salary range, job type, location type, full description, required skills, and posted date formatted as a human-readable relative date (e.g., "3 days ago")
3. IF a user navigates to `/jobs/{id}` with a non-existent job ID, THEN THE Job_Controller SHALL return a 404 error page displaying a message indicating the job was not found and a link to navigate back to the Job_Listing_Page
4. THE Job_Detail_Page SHALL include a "Back to Jobs" link that navigates to the Job_Listing_Page
5. IF a user navigates to `/jobs/{id}` where `{id}` is not a valid numeric identifier, THEN THE Job_Controller SHALL return a 404 error page
6. IF the job's company logo URL is not available, THEN THE Job_Detail_Page SHALL display a default placeholder image in place of the company logo

### Requirement 3: User Registration

**User Story:** As a new visitor, I want to create an account with my email address, so that I can access authenticated features of the platform.

#### Acceptance Criteria

1. WHEN a user navigates to `/register`, THE Auth_Controller SHALL return the Registration_Form
2. WHEN a user submits the Registration_Form with a valid name (1 to 255 characters), a valid email address (RFC 5322 format, maximum 255 characters), and a password (8 to 72 characters), THE Auth_Controller SHALL create a new User_Model record in the database, send an OTP to the provided email address, and redirect the user to the OTP verification page
3. IF a user submits the Registration_Form with an email that already exists in the database, THEN THE Auth_Controller SHALL display a validation error stating the email is already registered
4. IF a user submits the Registration_Form with a password shorter than 8 characters or longer than 72 characters, THEN THE Auth_Controller SHALL display a validation error stating the password length requirement
5. IF a user submits the Registration_Form with any required field (name, email, or password) left empty, THEN THE Auth_Controller SHALL display a validation error indicating which fields are required
6. IF a user submits the Registration_Form with an invalid email format, THEN THE Auth_Controller SHALL display a validation error stating the email format is invalid
7. WHEN the Auth_Controller creates a new user, THE User_Model SHALL store the password in a hashed format

### Requirement 4: Email OTP Verification

**User Story:** As a newly registered user, I want to verify my email address using a one-time password, so that the platform can confirm my identity.

#### Acceptance Criteria

1. WHEN a user completes registration, THE OTP_Service SHALL generate a 6-digit numeric OTP and send it to the user's email address
2. THE OTP_Service SHALL store the generated OTP in a hashed format with an expiration time of 10 minutes
3. WHEN a user submits a valid OTP within the expiration window, THE Auth_Controller SHALL mark the user's email as verified, log the user in via the Session_Guard, and redirect to the Job_Listing_Page
4. IF a user submits an incorrect OTP, THEN THE Auth_Controller SHALL display an error message indicating the OTP is invalid and decrement the remaining attempts from a maximum of 5 attempts per OTP
5. IF a user exceeds 5 incorrect OTP submission attempts for the current OTP, THEN THE Auth_Controller SHALL invalidate the current OTP, display an error message indicating the OTP has been locked, and require the user to request a new OTP
6. IF a user submits an OTP after the 10-minute expiration window, THEN THE Auth_Controller SHALL display an error message indicating the OTP has expired and prompt the user to request a new one
7. WHEN a user requests a new OTP, THE OTP_Service SHALL invalidate any previously issued OTP for that user and generate a new one
8. THE OTP_Service SHALL throttle OTP resend requests to a maximum of 3 requests per 15 minutes per email address
9. IF a user exceeds the OTP resend throttle limit of 3 requests per 15 minutes, THEN THE Auth_Controller SHALL display an error message indicating the resend limit has been reached and show the remaining wait time in minutes before the next request is allowed

### Requirement 5: User Login

**User Story:** As a registered user, I want to log in with my email and password, so that I can access my account.

#### Acceptance Criteria

1. WHEN a user navigates to `/login`, THE Auth_Controller SHALL return the Login_Form containing an email field and a password field
2. WHEN a user submits the Login_Form with valid credentials, THE Auth_Controller SHALL authenticate the user via the Session_Guard and redirect to the Job_Listing_Page
3. IF a user submits the Login_Form with an empty email field, an empty password field, or an email not in valid email format, THEN THE Auth_Controller SHALL display a validation error indicating the invalid fields without attempting authentication
4. IF a user submits the Login_Form with a valid email format and non-empty password that do not match any registered account, THEN THE Auth_Controller SHALL display a generic error message indicating the credentials are incorrect without revealing whether the email exists
5. IF a user with an unverified email submits the Login_Form with valid credentials, THEN THE Auth_Controller SHALL redirect the user to the OTP verification page and send a new OTP
6. IF a user submits the Login_Form and has exceeded 5 failed login attempts within a 15-minute window, THEN THE Auth_Controller SHALL reject the attempt and display an error message indicating the account is temporarily locked with the remaining lockout duration
7. WHEN a user clicks the logout action, THE Auth_Controller SHALL invalidate the session and redirect to the home page
8. IF an authenticated user navigates to `/login`, THEN THE Auth_Controller SHALL redirect the user to the Job_Listing_Page

### Requirement 6: MySQL Database Integration

**User Story:** As a developer, I want the application to use MySQL as the primary database, so that the platform can reliably store and query users and job data at scale.

#### Acceptance Criteria

1. THE Database_Connection SHALL use the MySQL driver configured via environment variables (DB_CONNECTION=mysql, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
2. WHEN the application starts, THE Database_Connection SHALL establish a connection to the configured MySQL instance and fail fast with an error if the connection cannot be established within 5 seconds
3. THE Job_Model SHALL be backed by a `job_listings` migration containing columns for title (string, max 255 characters), company_name (string, max 255 characters), company_logo_url (string, nullable, max 2048 characters), location (string, max 255 characters), salary_min (unsigned integer), salary_max (unsigned integer), job_type (string, max 50 characters), location_type (string, max 50 characters), description (text), skills (JSON), and timestamps
4. THE User_Model migration SHALL include an `otp` column (nullable string, max 6 characters) and an `otp_expires_at` column (nullable timestamp) for OTP verification
5. IF a database connection fails, THEN THE application SHALL display an error page that contains a general error message indicating the service is unavailable without exposing database host, port, credentials, or driver error details
6. WHEN the `job_listings` migration runs, THE Database_Connection SHALL create an index on the `job_type` column and an index on the `location_type` column to support filtering queries

### Requirement 7: Navigation and Layout Integration

**User Story:** As a user, I want consistent navigation across all pages, so that I can easily move between the home page, job listings, and authentication pages.

#### Acceptance Criteria

1. WHILE the user is not authenticated, THE navigation bar SHALL display a "Login" link navigating to `/login` and a "Register" link navigating to `/register`
2. WHILE the user is authenticated, THE navigation bar SHALL display the user's name (as stored in the name field of the User_Model, truncated to 20 characters maximum) and a "Logout" link instead of "Login" and "Register"
3. WHEN a user clicks "Jobs" in the navigation bar, THE application SHALL navigate to the Job_Listing_Page at `/jobs`
4. THE navigation bar SHALL be visible on every page of the application including the home page, Job_Listing_Page, Job_Detail_Page, and authentication pages
5. WHEN a user clicks "Logout" in the navigation bar, THE Auth_Controller SHALL invalidate the session and redirect the user to the home page
6. WHILE the viewport width is less than 768px, THE navigation bar SHALL collapse navigation links into a toggleable mobile menu accessible via a menu button
