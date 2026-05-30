# Implementation Plan: Job Hub Home Page

## Overview

This plan implements the Job Hub home page as a Laravel application with 10 distinct sections. Each section is built as an isolated Blade component with Tailwind CSS v4 styling, Alpine.js interactivity, and Chart.js for data visualization. The implementation follows an incremental approach: project structure and layout first, then individual sections from top to bottom, wiring data through the controller, and finally integration testing.

## Tasks

- [x] 1. Set up project structure, layout template, and design tokens
  - [x] 1.1 Configure Tailwind CSS v4 design tokens and base styles
    - Update `resources/css/app.css` with `@import 'tailwindcss'` and `@theme` block containing all design tokens (colors, font-family, border-radius) as defined in the design document
    - Add dark mode overrides using `@media (prefers-color-scheme: dark)` for background, foreground, primary, secondary, accent, border, and card colors
    - Ensure Inter font is imported via CDN link in the layout with `font-display: swap`
    - _Requirements: 1.1, 1.3, 1.4, 1.5, 14.1_

  - [x] 1.2 Create the base layout template (`resources/views/layouts/app.blade.php`)
    - Define HTML5 document structure with `lang="en"` attribute
    - Add meta tags: charset UTF-8, viewport with `width=device-width, initial-scale=1`
    - Add Inter font CDN link with `font-display: swap`
    - Add Lucide icons CDN reference
    - Include `@vite(['resources/css/app.css', 'resources/js/app.js'])` for asset injection
    - Add skip navigation link as the first focusable element: `<a href="#main-content" class="sr-only focus:not-sr-only ...">Skip to main content</a>`
    - Define `@yield('content')` slot within a `<main id="main-content">` element
    - _Requirements: 16.2, 13.7, 14.1, 14.2, 14.4_

  - [x] 1.3 Create the main home page view (`resources/views/home.blade.php`)
    - Extend `layouts.app`
    - Include all 10 section components in order within the `@section('content')` block
    - Apply max-width 1400px container with centered horizontal alignment and section padding (80px top/bottom, responsive horizontal padding)
    - _Requirements: 1.2, 1.6, 16.3_

  - [x] 1.4 Set up Alpine.js in the JavaScript entry point
    - Update `resources/js/app.js` to import and initialize Alpine.js
    - Register Alpine.data components: `mobileMenu`, `heroSearch`, `jobFilters`, `animatedJobCard`
    - _Requirements: 15.3, 15.4_

- [x] 2. Implement data models and HomeController
  - [x] 2.1 Create data transfer classes
    - Create `app/DataTransferObjects/JobData.php` with all properties: id, title, company, logoUrl, salaryMin, salaryMax, location, tags, applicantCount, isTrending, jobType, locationType, salaryRange, postedDate
    - Create `app/DataTransferObjects/CompanyData.php` with properties: name, logoUrl, culture, metrics, perks, isHiring
    - Create `app/DataTransferObjects/TestimonialData.php` with properties: name, role, avatarUrl, text, rating
    - Create `app/DataTransferObjects/CareerInsightsData.php` with properties: salaryData, hiringTrends, inDemandSkills
    - Create `app/DataTransferObjects/FeatureData.php` with properties: icon, title, description
    - Create `app/DataTransferObjects/FooterColumnData.php` with properties: heading, links
    - _Requirements: 6.4, 7.2, 10.3, 9.1, 9.2, 9.3, 8.2, 12.1_

  - [x] 2.2 Create HomeController with mock data
    - Create `app/Http/Controllers/HomeController.php` with `index()` method
    - Implement private methods: `getPopularSearchTerms()`, `getFeaturedJobs()`, `getCompanies()`, `getTestimonials()`, `getCareerInsights()`, `getAiFeatures()`, `getFooterLinks()`
    - Return `view('home', [...])` with all data arrays populated with realistic mock data
    - Ensure `getFeaturedJobs()` returns 6 items, `getCompanies()` returns 6 items (Stripe, Vercel, Linear, Notion, Airbnb, Figma), `getTestimonials()` returns 6 items
    - _Requirements: 16.4, 4.4, 6.1, 7.1, 10.1_

  - [x] 2.3 Register the route in `routes/web.php`
    - Add `Route::get('/', [HomeController::class, 'index'])->name('home');`
    - Remove or replace the existing default welcome route
    - _Requirements: 16.1_

- [x] 3. Implement Navigation Bar component
  - [x] 3.1 Create `resources/views/components/home/navigation-bar.blade.php`
    - Implement fixed-position `<nav>` element with high z-index
    - Display "Job Hub" logo text on the left
    - Display center menu links: Jobs, Companies, Insights, Resume
    - Display right-side elements: search icon (Lucide), notifications bell icon (Lucide), "Sign In" button
    - Implement mobile menu toggle using `x-data="mobileMenu"` Alpine.js component
    - Hide desktop menu links and show hamburger toggle below 768px
    - Implement mobile menu panel with `x-show="open"` and transition within 300ms
    - Use semantic `<nav>` element with appropriate ARIA labels
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 13.4, 15.2_

- [x] 4. Implement Hero Section component
  - [x] 4.1 Create `resources/views/components/home/hero-section.blade.php`
    - Display headline "Find work that moves you forward" in a prominent heading
    - Implement dual-input search bar with `x-data="heroSearch"` Alpine.js component
    - Add "Job title" input (maxlength 100) bound to `x-model="jobTitle"`
    - Add "Location" input (maxlength 100) bound to `x-model="location"`
    - Add search submit button with click handler `@click="submit()"`
    - Display validation error message when job title is empty on submit using `x-show="error"`
    - Render popular search terms from `$popularSearchTerms` prop as clickable elements with `@click="setPopularTerm(term)"`
    - Implement animated job card preview (visible only at >= 1024px) using `x-data="animatedJobCard"` with `x-show="visible"` and fade-in transition
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 4.9, 15.3_

- [x] 5. Implement Job Discovery Filters component
  - [x] 5.1 Create `resources/views/components/home/job-discovery-filters.blade.php`
    - Implement filter UI using `x-data="jobFilters"` Alpine.js component
    - Display four filter categories with all options: Job Type (Full-time, Part-time, Contract, Freelance, Internship), Location (Remote, Hybrid, On-site), Salary Range ($0–$50k, $50k–$100k, $100k–$150k, $150k+), Posted Date (Last 24 hours, Last 7 days, Last 30 days, All time)
    - Highlight selected options with primary color background using Alpine.js reactive classes
    - Display active filter tags below categories (max 12) with remove buttons
    - Implement `toggleFilter()`, `removeTag()` logic for AND-based filtering
    - Display "No results found" message when `filteredJobs` is empty
    - Ensure filter updates happen without page reload within 300ms
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 15.1_

- [x] 6. Implement Featured Jobs Grid component
  - [x] 6.1 Create `resources/views/components/home/featured-jobs.blade.php`
    - Accept `$featuredJobs` prop (array of 6 JobData items)
    - Render responsive grid: 1-column below 768px, 2-column 768–1023px, 3-column at 1024px+
    - Display each job card with: company logo (48x48px max, with `onerror` fallback to placeholder SVG), job title (truncated at 60 chars), company name, salary range "$XX,XXX - $XXX,XXX", location, up to 3 tags, applicant count, and "Apply" button
    - Display "Trending" badge on cards where `isTrending` is true
    - Implement logo fallback using `onerror="this.src='/placeholder-logo.svg'"` or similar
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 2.1, 2.2, 2.3_

- [~] 7. Checkpoint - Verify core sections render correctly
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Implement Company Showcase component
  - [x] 8.1 Create `resources/views/components/home/company-showcase.blade.php`
    - Accept `$companies` prop (array of 6 CompanyData items)
    - Render responsive grid: 1-column below 768px, 2-column 768–1023px, 3-column at 1024px+
    - Display each company card with: logo, name, culture description (max 120 chars), up to 3 metrics (label + value), up to 4 perks, and hiring status
    - Display "Hiring" badge on cards where `isHiring` is true
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 2.1, 2.2, 2.3_

- [x] 9. Implement AI Resume Matching component
  - [x] 9.1 Create `resources/views/components/home/ai-resume-matching.blade.php`
    - Accept `$aiFeatures` prop (array of 3 FeatureData items)
    - Implement two-column layout: features list on left, resume analysis card on right
    - Display section heading and subheading introducing AI resume matching
    - Render 3 features with Lucide icon, title, and description (max 120 chars)
    - Create resume analysis card with at least 3 progress bars showing category name and percentage (0–100%)
    - Stack columns vertically below 768px (features above analysis card)
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 10. Implement Career Insights component
  - [x] 10.1 Create `resources/views/components/home/career-insights.blade.php`
    - Accept `$careerInsights` prop (CareerInsightsData)
    - Implement lazy loading of Chart.js using Intersection Observer API
    - Create salary comparison bar chart with labeled axes (X: 4+ job roles, Y: salary in dollars)
    - Create hiring trends line chart with labeled axes (X: 6+ months, Y: hiring activity metric)
    - Display top 5 in-demand skills with name and visual progress indicator (0–100%)
    - Implement responsive layout: stacked vertically below 768px, charts side-by-side at 1024px+
    - Add fallback text content if Chart.js fails to load
    - Pass chart data from PHP to JavaScript using `@json()` directive or data attributes
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 14.3_

- [x] 11. Implement Testimonials component
  - [x] 11.1 Create `resources/views/components/home/testimonials.blade.php`
    - Accept `$testimonials` prop (array of 6 TestimonialData items)
    - Render responsive grid: 1-column below 768px, 2-column 768–1023px, 3-column at 1024px+
    - Display each testimonial card with: star rating (1–5 filled stars), testimonial text (max 200 chars), circular user avatar, user name, and user role
    - Use accessible star rating markup with appropriate ARIA labels
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 2.1, 2.2, 2.3_

- [x] 12. Implement Call to Action component
  - [x] 12.1 Create `resources/views/components/home/call-to-action.blade.php`
    - Apply gradient background from primary color to primary at 80% opacity (left to right)
    - Display headline "Ready to find your next opportunity?"
    - Display supporting description text below headline
    - Display two buttons: "Get Started" (primary style) and "Learn More" (secondary style)
    - Center-align all content
    - Stack buttons vertically below 768px
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

- [x] 13. Implement Footer component
  - [x] 13.1 Create `resources/views/components/home/footer.blade.php`
    - Accept `$footerLinks` prop (array of FooterColumnData items)
    - Render navigation links in 3+ labeled columns (each with heading and 3+ links)
    - Display 3+ social media icons using Lucide icons, each opening in new tab (`target="_blank" rel="noopener noreferrer"`)
    - Display copyright with platform name and current year using `{{ date('Y') }}`
    - Implement responsive layout: stacked vertically below 768px, horizontal multi-column grid at 1024px+
    - Use semantic `<footer>` element
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5, 12.6, 13.4_

- [x] 14. Accessibility and final polish
  - [x] 14.1 Add ARIA attributes and accessibility enhancements across all components
    - Ensure all interactive elements without visible text have descriptive `aria-label` attributes
    - Verify semantic HTML usage: `<nav>`, `<main>`, `<section>`, `<article>`, `<header>`, `<footer>`
    - Add `role` attributes where semantic elements are insufficient
    - Ensure all `<img>` tags have appropriate `alt` text (max 150 chars for informational, `alt=""` for decorative)
    - Verify focus indicators: 2px outline with 3:1 contrast ratio on all focusable elements
    - Ensure logical tab order matches visual reading order (top-left to bottom-right)
    - Verify no focus traps exist in any component
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5, 13.6, 13.8_

  - [x] 14.2 Verify responsive layout and no horizontal scroll
    - Test all sections render correctly at 320px, 768px, 1024px, and 1400px breakpoints
    - Ensure no horizontal scrollbar appears at any width from 320px to 2560px
    - Verify content does not overlap or truncate when crossing breakpoint thresholds
    - Verify max-width 1400px container centers with equal margins on large viewports
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

- [ ] 15. Write automated tests
  - [ ]* 15.1 Write unit tests for HomeController
    - Test `index()` returns a view instance with name 'home'
    - Test view data contains all required keys: popularSearchTerms, featuredJobs, companies, testimonials, careerInsights, aiFeatures, footerLinks
    - Test `featuredJobs` contains exactly 6 items
    - Test `companies` contains exactly 6 items
    - Test `testimonials` contains exactly 6 items
    - Test `popularSearchTerms` contains between 3 and 8 items
    - _Requirements: 16.1, 16.4_

  - [ ]* 15.2 Write feature tests for page rendering
    - Test GET `/` returns HTTP 200
    - Test response contains expected HTML landmarks: `<nav>`, `<main>`, `<footer>`
    - Test response contains skip navigation link
    - Test response contains viewport meta tag
    - Test response contains "Job Hub" text
    - Test response contains "Find work that moves you forward" headline
    - Test response contains all 10 section components rendered
    - _Requirements: 16.1, 13.4, 13.7, 16.3_

  - [ ]* 15.3 Write feature tests for data integrity
    - Test view receives `featuredJobs` with correct data shape (each item has title, company, salary fields)
    - Test view receives `companies` with correct company names (Stripe, Vercel, Linear, Notion, Airbnb, Figma)
    - Test view receives `careerInsights` with salaryData (4+ items), hiringTrends (6+ items), inDemandSkills (5 items)
    - _Requirements: 6.4, 7.1, 9.1, 9.2, 9.3_

- [x] 16. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- The design explicitly states property-based testing does not apply to this UI-focused feature
- Unit tests and feature tests validate controller logic and rendered HTML structure
- All sections use Blade components for isolation and maintainability
- Alpine.js handles all client-side interactivity (no jQuery or vanilla JS event listeners)
- Chart.js is lazy-loaded via Intersection Observer for performance

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.4", "2.1"] },
    { "id": 1, "tasks": ["1.2", "2.2"] },
    { "id": 2, "tasks": ["1.3", "2.3"] },
    { "id": 3, "tasks": ["3.1", "4.1", "5.1", "6.1"] },
    { "id": 4, "tasks": ["8.1", "9.1", "10.1", "11.1"] },
    { "id": 5, "tasks": ["12.1", "13.1"] },
    { "id": 6, "tasks": ["14.1", "14.2"] },
    { "id": 7, "tasks": ["15.1", "15.2", "15.3"] }
  ]
}
```
