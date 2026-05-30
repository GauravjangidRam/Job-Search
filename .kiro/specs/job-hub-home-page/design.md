# Design Document: Job Hub Home Page

## Overview

The Job Hub Home Page is a Laravel-based landing page composed of 10 distinct sections, each implemented as a Blade partial/component. The page uses Tailwind CSS v4 for styling, Alpine.js for client-side interactivity, and Chart.js for data visualization. The architecture follows Laravel conventions with a layout template, section-based Blade components, and a dedicated controller to supply view data.

The design prioritizes:
- **Component isolation**: Each section is a self-contained Blade component with its own data contract
- **Progressive enhancement**: Core content renders server-side; interactivity layers on via Alpine.js
- **Performance**: Chart.js loads lazily when the Career Insights section enters the viewport
- **Accessibility**: Semantic HTML, ARIA attributes, keyboard navigation, and WCAG 2.1 AA compliance
- **Responsive design**: Mobile-first approach with breakpoints at 768px, 1024px, and 1400px

## Architecture

```mermaid
graph TD
    A[Browser Request GET /] --> B[Laravel Router]
    B --> C[HomeController@index]
    C --> D[Collect View Data]
    D --> E[Return home.blade.php View]
    E --> F[layouts/app.blade.php]
    F --> G[Blade Components/Partials]
    
    G --> G1[navigation-bar]
    G --> G2[hero-section]
    G --> G3[job-discovery-filters]
    G --> G4[featured-jobs]
    G --> G5[company-showcase]
    G --> G6[ai-resume-matching]
    G --> G7[career-insights]
    G --> G8[testimonials]
    G --> G9[call-to-action]
    G --> G10[footer]

    subgraph "Client-Side"
        H[Alpine.js] --> H1[Mobile Menu Toggle]
        H --> H2[Filter State Management]
        H --> H3[Hero Search Logic]
        H --> H4[Animated Job Card]
        I[Chart.js] --> I1[Salary Bar Chart]
        I --> I2[Hiring Trends Line Chart]
    end

    subgraph "Asset Pipeline"
        J[Vite] --> J1[Tailwind CSS v4]
        J --> J2[Alpine.js Bundle]
        J --> J3[App JS/CSS]
    end
```

### Request Flow

1. Browser sends GET request to `/`
2. Laravel router dispatches to `HomeController@index`
3. Controller assembles static/mock data for all sections
4. Controller returns the `home` view with data
5. Blade renders the layout template with all section components
6. Vite injects compiled CSS and JS assets
7. Alpine.js initializes interactive components on DOMContentLoaded
8. Chart.js loads lazily when Career Insights section is scrolled into view

## Components and Interfaces

### Blade Template Structure

```
resources/views/
├── layouts/
│   └── app.blade.php              # Base HTML document layout
├── home.blade.php                  # Main home page view (assembles sections)
└── components/
    └── home/
        ├── navigation-bar.blade.php
        ├── hero-section.blade.php
        ├── job-discovery-filters.blade.php
        ├── featured-jobs.blade.php
        ├── company-showcase.blade.php
        ├── ai-resume-matching.blade.php
        ├── career-insights.blade.php
        ├── testimonials.blade.php
        ├── call-to-action.blade.php
        └── footer.blade.php
```

### Layout Template (`layouts/app.blade.php`)

Responsibilities:
- HTML5 document structure with `lang` attribute
- Meta tags: charset, viewport, description
- Font loading: Inter from CDN with `font-display: swap`
- Lucide icons: CDN or bundled reference
- Vite asset injection: `@vite(['resources/css/app.css', 'resources/js/app.js'])`
- Dark mode detection via `prefers-color-scheme` media query
- Skip navigation link as first focusable element
- `@yield('content')` slot for page content

### Controller Interface

```php
// app/Http/Controllers/HomeController.php

class HomeController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('home', [
            'popularSearchTerms' => $this->getPopularSearchTerms(),
            'featuredJobs'       => $this->getFeaturedJobs(),
            'companies'          => $this->getCompanies(),
            'testimonials'       => $this->getTestimonials(),
            'careerInsights'     => $this->getCareerInsights(),
            'aiFeatures'         => $this->getAiFeatures(),
            'footerLinks'        => $this->getFooterLinks(),
        ]);
    }
}
```

### Component Data Contracts

Each Blade component receives typed data via props:

| Component | Props | Type |
|-----------|-------|------|
| navigation-bar | — | Static content |
| hero-section | `$popularSearchTerms` | `array<string>` (3–8 items) |
| job-discovery-filters | — | Alpine.js manages state client-side |
| featured-jobs | `$featuredJobs` | `array<JobData>` (6 items) |
| company-showcase | `$companies` | `array<CompanyData>` (6 items) |
| ai-resume-matching | `$aiFeatures` | `array<FeatureData>` (3 items) |
| career-insights | `$careerInsights` | `CareerInsightsData` |
| testimonials | `$testimonials` | `array<TestimonialData>` (6 items) |
| call-to-action | — | Static content |
| footer | `$footerLinks` | `array<FooterColumnData>` |

### Alpine.js Component Interfaces

```javascript
// Mobile Navigation Toggle
Alpine.data('mobileMenu', () => ({
    open: false,
    toggle() { this.open = !this.open; }
}));

// Job Discovery Filters
Alpine.data('jobFilters', () => ({
    filters: {
        jobType: [],      // ['Full-time', 'Part-time', ...]
        location: [],     // ['Remote', 'Hybrid', ...]
        salary: [],       // ['$0–$50k', '$50k–$100k', ...]
        postedDate: []    // ['Last 24 hours', ...]
    },
    get activeTags() { /* returns flat array of active filters */ },
    toggleFilter(category, value) { /* add/remove filter */ },
    removeTag(category, value) { /* remove specific filter */ },
    get filteredJobs() { /* returns jobs matching all active filters */ }
}));

// Hero Search
Alpine.data('heroSearch', () => ({
    jobTitle: '',
    location: '',
    error: '',
    setPopularTerm(term) { this.jobTitle = term; },
    submit() {
        if (!this.jobTitle.trim()) {
            this.error = 'Job title is required';
            return;
        }
        // Navigate to search results
    }
}));

// Animated Job Card (Hero)
Alpine.data('animatedJobCard', () => ({
    visible: false,
    init() { setTimeout(() => this.visible = true, 500); }
}));
```

## Data Models

### JobData

```php
class JobData
{
    public function __construct(
        public string $id,
        public string $title,          // max 60 chars
        public string $company,
        public ?string $logoUrl,       // max 48x48px, nullable for placeholder
        public string $salaryMin,      // formatted: "$XX,XXX"
        public string $salaryMax,      // formatted: "$XXX,XXX"
        public string $location,
        public array $tags,            // max 3 items
        public int $applicantCount,
        public bool $isTrending,
        public string $jobType,        // Full-time, Part-time, etc.
        public string $locationType,   // Remote, Hybrid, On-site
        public string $salaryRange,    // $0–$50k, $50k–$100k, etc.
        public string $postedDate,     // Last 24 hours, Last 7 days, etc.
    ) {}
}
```

### CompanyData

```php
class CompanyData
{
    public function __construct(
        public string $name,
        public string $logoUrl,
        public string $culture,        // max 120 chars
        public array $metrics,         // max 3 items [{label, value}]
        public array $perks,           // max 4 items
        public bool $isHiring,
    ) {}
}
```

### TestimonialData

```php
class TestimonialData
{
    public function __construct(
        public string $name,
        public string $role,
        public string $avatarUrl,
        public string $text,           // max 200 chars
        public int $rating,            // 1–5
    ) {}
}
```

### CareerInsightsData

```php
class CareerInsightsData
{
    public function __construct(
        public array $salaryData,      // [{role: string, salary: int}] min 4 items
        public array $hiringTrends,    // [{month: string, value: int}] min 6 items
        public array $inDemandSkills,  // [{name: string, percentage: int}] 5 items
    ) {}
}
```

### FeatureData

```php
class FeatureData
{
    public function __construct(
        public string $icon,           // Lucide icon name
        public string $title,
        public string $description,    // max 120 chars
    ) {}
}
```

### FooterColumnData

```php
class FooterColumnData
{
    public function __construct(
        public string $heading,
        public array $links,           // [{label: string, url: string}] min 3 items
    ) {}
}
```

### Design Token Configuration (Tailwind CSS v4)

```css
/* resources/css/app.css */
@import 'tailwindcss';

@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif,
        'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';

    --color-primary: #ea580c;
    --color-primary-light: #fb923c;
    --color-accent: #fed7aa;
    --color-accent-dark: #78350f;

    --color-background: #fafaf9;
    --color-foreground: #1c1917;
    --color-secondary: #f5f5f4;
    --color-muted: #78716c;
    --color-border: #e7e5e4;
    --color-card: #ffffff;

    /* Dark mode overrides applied via @media (prefers-color-scheme: dark) */
    --color-background-dark: #1c1917;
    --color-foreground-dark: #fafaf9;
    --color-primary-dark: #fb923c;
    --color-secondary-dark: #44403c;
    --color-accent-dark-mode: #78350f;
    --color-border-dark: #44403c;
    --color-card-dark: #292524;

    --radius-card: 0.5rem;
}
```

## Error Handling

| Scenario | Handling Strategy |
|----------|-------------------|
| View/layout template not found | Laravel returns 500 error with error page |
| Company logo fails to load | `<img>` `onerror` handler swaps to placeholder SVG icon |
| Chart.js fails to load (CDN) | Intersection Observer callback catches error; section shows static fallback text |
| Alpine.js fails to initialize | Page remains functional with server-rendered content; interactive features degrade gracefully |
| Empty job title on search submit | Alpine.js validation prevents submission; displays inline error message |
| No jobs match active filters | Display "No results found" message within the job listings area |
| Font loading failure | `font-display: swap` ensures system font renders immediately; Inter loads asynchronously |
| JavaScript disabled | Core content (all 10 sections) renders via server-side Blade; filters and animations are non-functional but content is readable |

## Testing Strategy

### Why Property-Based Testing Does Not Apply

This feature is primarily **UI rendering and layout** — Blade templates producing HTML with Tailwind CSS classes, Alpine.js for client-side interactivity, and Chart.js for visualization. There are no pure functions with meaningful input variation, no parsers, serializers, or algorithmic logic that would benefit from property-based testing. The acceptance criteria describe visual layout rules, responsive breakpoints, and UI interactions rather than data transformations.

Appropriate testing strategies for this feature:

### Unit Tests (PHPUnit)

- **HomeController**: Verify `index()` returns a view with expected data keys and correct data shapes
- **Data classes**: Verify construction and validation of `JobData`, `CompanyData`, `TestimonialData`, `CareerInsightsData`
- **Route**: Verify GET `/` returns HTTP 200 and renders the correct view

### Feature Tests (Laravel HTTP Tests)

- **Page rendering**: `$this->get('/')->assertStatus(200)->assertViewIs('home')`
- **View data**: Assert view receives all required data arrays with correct item counts
- **Blade components**: Assert response contains expected HTML landmarks (`<nav>`, `<main>`, `<footer>`)
- **Accessibility landmarks**: Assert skip-nav link, semantic elements, ARIA attributes present in rendered HTML
- **Dark mode**: Assert CSS custom properties or class-based dark mode tokens are present
- **Responsive meta**: Assert viewport meta tag is present

### Browser Tests (Laravel Dusk or Playwright)

- **Responsive layout**: Test grid column counts at 320px, 768px, 1024px, and 1400px breakpoints
- **No horizontal scroll**: Assert `document.documentElement.scrollWidth <= document.documentElement.clientWidth` at all breakpoints
- **Mobile menu**: Click toggle, verify menu visibility transitions
- **Filter interaction**: Select filters, verify tags appear, verify job cards update
- **Hero search validation**: Submit empty form, verify error message appears
- **Popular search terms**: Click term, verify input field populated
- **Chart lazy loading**: Scroll to Career Insights, verify Chart.js canvas renders
- **Keyboard navigation**: Tab through all interactive elements, verify focus order
- **Dark mode**: Toggle `prefers-color-scheme`, verify color changes

### Accessibility Testing

- **axe-core**: Automated WCAG 2.1 AA audit on rendered page
- **Contrast checks**: Verify 4.5:1 ratio for normal text, 3:1 for large text
- **Screen reader testing**: Manual verification of reading order and ARIA labels
- **Focus indicators**: Verify 2px outline with 3:1 contrast on all focusable elements

### Visual Regression Testing

- **Percy or Chromatic**: Capture snapshots at each breakpoint for both light and dark modes
- **Component-level snapshots**: Individual section screenshots for isolated regression detection
