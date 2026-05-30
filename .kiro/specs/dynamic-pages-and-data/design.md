# Design Document: Dynamic Pages and Data

## Overview

This design transforms the Job Hub application from a static-data prototype into a fully dynamic, database-driven platform. The current `HomeController` returns hardcoded arrays of DTOs; this feature replaces those with Eloquent queries against three new database tables (`companies`, `testimonials`, `career_insights`), adds a `company_id` foreign key to `job_listings`, introduces four new pages (Companies listing, Company detail, Career Insights, Resume), wires the home page hero search to the existing `JobController` search, and adds a job application flow with authentication gating.

The architecture follows Laravel conventions already established in the project: Eloquent models, Blade views with Alpine.js interactivity, standard controllers, and form request validation.

## Architecture

```mermaid
graph TD
    subgraph Browser
        A[Blade Views + Alpine.js]
    end

    subgraph Routes
        R1[GET /]
        R2[GET /companies]
        R3[GET /companies/{slug}]
        R4[GET /insights]
        R5[GET /resume]
        R6[GET /jobs/{job}/apply]
        R7[POST /jobs/{job}/apply]
    end

    subgraph Controllers
        HC[HomeController]
        CC[CompanyController]
        IC[CareerInsightController]
        RC[ResumeController]
        JC[JobController]
    end

    subgraph Models
        M1[Company]
        M2[Testimonial]
        M3[CareerInsight]
        M4[JobListing]
        M5[JobApplication]
        M6[User]
    end

    subgraph Database
        DB1[(companies)]
        DB2[(testimonials)]
        DB3[(career_insights)]
        DB4[(job_listings)]
        DB5[(job_applications)]
    end

    A --> R1 --> HC
    A --> R2 --> CC
    A --> R3 --> CC
    A --> R4 --> IC
    A --> R5 --> RC
    A --> R6 --> JC
    A --> R7 --> JC

    HC --> M1 & M2 & M3 & M4
    CC --> M1 & M4
    IC --> M3
    JC --> M4 & M5

    M1 --> DB1
    M2 --> DB2
    M3 --> DB3
    M4 --> DB4
    M5 --> DB5
    M4 -.->|company_id FK| DB1
```

### Design Decisions

1. **Separate controllers per resource** — `CompanyController`, `CareerInsightController`, and `ResumeController` keep responsibilities focused and follow Laravel resource conventions.
2. **Eloquent models over raw queries** — Consistent with the existing `JobListing` and `User` models; enables relationships, casts, and scopes.
3. **Slug-based company URLs** — SEO-friendly and human-readable; generated via a model boot event with uniqueness enforcement.
4. **Job applications as a pivot-like table** — A `job_applications` table with `user_id` + `job_listing_id` unique constraint prevents duplicate applications and records timestamps.
5. **UI Avatars for logo fallback** — External service (`ui-avatars.com`) generates deterministic placeholder images; handled in a Blade component for reuse.
6. **Alpine.js for client-side filtering** — Continues the existing pattern (see `app.js` `jobFilters` component) for the companies page industry/hiring filter.

## Components and Interfaces

### New Controllers

| Controller | Methods | Responsibility |
|---|---|---|
| `CompanyController` | `index()`, `show($slug)` | Companies listing (paginated, filterable) and detail page |
| `CareerInsightController` | `index()` | Career insights page with grouped data |
| `ResumeController` | `index()` | Static resume tips/templates page |

### Modified Controllers

| Controller | Changes |
|---|---|
| `HomeController` | Replace all hardcoded data methods with Eloquent queries |
| `JobController` | Add `apply()` (GET) and `submitApplication()` (POST) methods |

### New Models

| Model | Table | Key Relationships |
|---|---|---|
| `Company` | `companies` | `hasMany(JobListing)` |
| `Testimonial` | `testimonials` | — |
| `CareerInsight` | `career_insights` | — |
| `JobApplication` | `job_applications` | `belongsTo(User)`, `belongsTo(JobListing)` |

### Modified Models

| Model | Changes |
|---|---|
| `JobListing` | Add `company_id` fillable, add `belongsTo(Company)` relationship |

### New Routes

```php
// Companies
Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{slug}', [CompanyController::class, 'show'])->name('companies.show');

// Career Insights
Route::get('/insights', [CareerInsightController::class, 'index'])->name('insights.index');

// Resume
Route::get('/resume', [ResumeController::class, 'index'])->name('resume.index');

// Job Application (authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');
    Route::post('/jobs/{job}/apply', [JobController::class, 'submitApplication'])->name('jobs.submitApplication');
});
```

### Blade Components

| Component | Purpose |
|---|---|
| `<x-company-logo>` | Renders company logo with UI Avatars fallback and `onerror` JS handler |
| `<x-empty-state>` | Reusable empty-state message block |

### New Views

| View | Route |
|---|---|
| `companies/index.blade.php` | `/companies` |
| `companies/show.blade.php` | `/companies/{slug}` |
| `insights/index.blade.php` | `/insights` |
| `resume/index.blade.php` | `/resume` |
| `jobs/apply.blade.php` | `/jobs/{job}/apply` |

## Data Models

### companies table

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK, auto-increment |
| `name` | varchar(255) | NOT NULL |
| `slug` | varchar(255) | NOT NULL, UNIQUE |
| `logo_url` | varchar(2048) | NULLABLE |
| `website_url` | varchar(2048) | NULLABLE |
| `description` | text | NULLABLE |
| `culture` | text | NULLABLE |
| `employee_count` | unsigned int | NULLABLE |
| `founded_year` | unsigned int | NULLABLE, CHECK 1800–current year |
| `industry` | varchar(100) | NULLABLE |
| `is_hiring` | boolean | DEFAULT false |
| `metrics` | json | NULLABLE |
| `perks` | json | NULLABLE |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes:** `slug` (unique), `is_hiring`, `industry`

### testimonials table

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK, auto-increment |
| `name` | varchar(100) | NOT NULL |
| `role` | varchar(100) | NOT NULL |
| `company` | varchar(100) | NOT NULL |
| `avatar_url` | varchar(2048) | NULLABLE |
| `text` | text (max 1000) | NOT NULL |
| `rating` | unsigned int | NOT NULL, CHECK 1–5 |
| `is_featured` | boolean | DEFAULT false |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes:** `is_featured`

### career_insights table

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK, auto-increment |
| `type` | enum('salary','trend','skill') | NOT NULL |
| `label` | varchar(100) | NOT NULL |
| `value` | varchar(255) | NOT NULL |
| `sort_order` | unsigned int | NOT NULL |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes:** `type`, composite (`type`, `sort_order`)

### job_applications table

| Column | Type | Constraints |
|---|---|---|
| `id` | bigint unsigned | PK, auto-increment |
| `user_id` | bigint unsigned | FK → users.id, NOT NULL |
| `job_listing_id` | bigint unsigned | FK → job_listings.id, NOT NULL |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Indexes:** UNIQUE(`user_id`, `job_listing_id`)

### job_listings table modification

Add column: `company_id` (bigint unsigned, NULLABLE, FK → companies.id, ON DELETE SET NULL)

### Eloquent Model Definitions

**Company Model:**
```php
class Company extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo_url', 'website_url', 'description',
        'culture', 'employee_count', 'founded_year', 'industry',
        'is_hiring', 'metrics', 'perks',
    ];

    protected function casts(): array
    {
        return [
            'metrics' => 'array',
            'perks' => 'array',
            'is_hiring' => 'boolean',
            'employee_count' => 'integer',
            'founded_year' => 'integer',
        ];
    }

    public function jobListings(): HasMany
    {
        return $this->hasMany(JobListing::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Company $company) {
            $company->slug = $company->slug ?? static::generateUniqueSlug($company->name);
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 2;
        while (static::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }
        return $slug;
    }
}
```

**CareerInsight Model:**
```php
class CareerInsight extends Model
{
    protected $fillable = ['type', 'label', 'value', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
```

**Testimonial Model:**
```php
class Testimonial extends Model
{
    protected $fillable = ['name', 'role', 'company', 'avatar_url', 'text', 'rating', 'is_featured'];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
        ];
    }
}
```

**JobApplication Model:**
```php
class JobApplication extends Model
{
    protected $fillable = ['user_id', 'job_listing_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }
}
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Slug format correctness

*For any* company name string, the generated slug SHALL be entirely lowercase, use only alphanumeric characters and single hyphens as separators, contain no consecutive hyphens, and not start or end with a hyphen.

**Validates: Requirements 1.2**

### Property 2: Slug uniqueness under duplication

*For any* set of N companies created with the same name, all N generated slugs SHALL be distinct, with the first being the base slug and subsequent ones having incrementing numeric suffixes (-2, -3, ..., -N).

**Validates: Requirements 1.3**

### Property 3: JSON metrics and perks round-trip

*For any* valid metrics object and perks array (up to 50 strings, each max 255 characters), storing them in a Company record and then reloading that record SHALL produce values identical to the originals.

**Validates: Requirements 1.4**

### Property 4: Cascade null on company deletion

*For any* company with N associated job listings (where N >= 0), deleting that company SHALL result in all N job listings having a null company_id and all N job listings still existing in the database.

**Validates: Requirements 1.6**

### Property 5: Featured testimonials query correctness

*For any* set of testimonials with varying `is_featured` flags and `created_at` timestamps, querying featured testimonials SHALL return only records where `is_featured` is true, ordered by `created_at` descending, with a maximum of 6 results.

**Validates: Requirements 2.2, 10.3**

### Property 6: Testimonial rating validation

*For any* integer value, the testimonial validation SHALL accept the value if and only if it is between 1 and 5 inclusive; all other integer values SHALL be rejected with a validation error.

**Validates: Requirements 2.3, 2.4**

### Property 7: Career insights grouping and ordering

*For any* set of career insight records with varying types and sort_order values, grouping by type SHALL produce exactly the groups present in the data (subset of salary, trend, skill), each group ordered by sort_order ascending, with a maximum of 20 records per group.

**Validates: Requirements 3.2, 10.4**

### Property 8: Companies alphabetical pagination

*For any* set of N companies, the companies listing page SHALL return them sorted alphabetically by name, with exactly min(12, remaining) companies per page and correct total page count of ceil(N/12).

**Validates: Requirements 4.1, 4.2**

### Property 9: Company placeholder generation determinism

*For any* company name, the generated placeholder SHALL use the first letter of each word (up to 2 initials), and the same company name SHALL always produce the same placeholder URL (deterministic background color).

**Validates: Requirements 4.3, 11.2**

### Property 10: Company jobs ordering

*For any* company with N associated job listings having varying `created_at` timestamps, the company detail page SHALL display all N listings ordered by `created_at` descending (most recent first).

**Validates: Requirements 5.2**

### Property 11: Search input handling

*For any* non-empty, non-whitespace string (max 100 characters), submitting the hero search SHALL redirect to `/jobs?search={term}`. *For any* empty or whitespace-only string, submitting SHALL be rejected with a validation error and no navigation.

**Validates: Requirements 8.1, 8.2**

### Property 12: Search filter correctness

*For any* set of job listings and any search term, all results returned by the search SHALL contain the search term (case-insensitive partial match) in at least one of: title, company_name, or description. No job listing that contains the term in any of those fields SHALL be excluded from results.

**Validates: Requirements 8.3**

### Property 13: Duplicate application prevention

*For any* user who has already applied to a specific job listing, attempting to view the application page for that job SHALL show an "already applied" indicator and the confirm button SHALL be disabled.

**Validates: Requirements 9.7**

### Property 14: Home page query limits

*For any* database state with N job listings, M companies (some hiring, some not), the home page SHALL display at most 6 job listings (ordered by created_at desc) and at most 6 companies where is_hiring is true (ordered by name asc).

**Validates: Requirements 10.1, 10.2**

## Error Handling

### HTTP Error Responses

| Scenario | Response | User Experience |
|---|---|---|
| Company slug not found | 404 | "Company not found" page with link to companies listing |
| Job ID not found on apply page | 404 | Error message with link back to job listings |
| Unauthenticated access to apply page | 302 → /login | Redirect to login, return to apply page after auth |
| Invalid search input (whitespace-only) | No navigation | Inline validation error below search input |
| Invalid testimonial rating | 422 | Validation error message indicating which field failed |
| Database query returns empty results | 200 | Empty-state message in the relevant section |

### Validation Rules

**Testimonial creation:**
- `name`: required, string, max 100
- `role`: required, string, max 100
- `company`: required, string, max 100
- `text`: required, string, max 1000
- `rating`: required, integer, between 1 and 5

**Company creation:**
- `name`: required, string, max 255
- `founded_year`: nullable, integer, between 1800 and current year
- `perks`: nullable, array, max 50 items, each string max 255

**Search input:**
- `title` / `search`: required (non-empty, non-whitespace), string, max 100

### Graceful Degradation

- If the `companies`, `testimonials`, or `career_insights` tables are empty, the home page renders normally with empty sections (no errors).
- If a company logo fails to load client-side, the `onerror` handler swaps to the UI Avatars placeholder without page reload.
- If chart rendering fails on the insights page, accessible fallback content (tables/lists) is displayed.

## Testing Strategy

### Unit Tests (PHPUnit)

Unit tests cover specific examples, edge cases, and integration points:

- **Model tests**: Verify Eloquent relationships, casts, and fillable attributes
- **Controller tests**: HTTP response codes, view data, redirects
- **Validation tests**: Specific invalid inputs for testimonials, search, company fields
- **Seeder tests**: Verify seeders run without errors and produce expected record counts
- **Edge cases**: Empty database sections, 404 responses, duplicate application attempts

### Property-Based Tests (PHPUnit with custom data providers)

Property-based tests verify universal properties across generated inputs. Since PHP's ecosystem doesn't have a mature PBT library equivalent to QuickCheck, we'll use PHPUnit data providers with Faker to generate diverse inputs (minimum 100 iterations per property).

**Library:** PHPUnit with `fakerphp/faker` for input generation, using `@dataProvider` methods that yield 100+ cases.

**Configuration:**
- Each property test runs a minimum of 100 iterations
- Each test is tagged with a comment referencing the design property
- Tag format: `Feature: dynamic-pages-and-data, Property {number}: {property_text}`

**Property tests to implement:**

| Property | Test Class | What's Generated |
|---|---|---|
| 1: Slug format | `CompanySlugTest` | Random company names with unicode, spaces, special chars |
| 2: Slug uniqueness | `CompanySlugTest` | Repeated identical names (2–10 duplicates) |
| 3: JSON round-trip | `CompanyJsonTest` | Random metrics objects and perks arrays |
| 4: Cascade null | `CompanyDeletionTest` | Companies with 0–20 associated job listings |
| 5: Featured testimonials | `TestimonialQueryTest` | Sets of 0–30 testimonials with random featured flags |
| 6: Rating validation | `TestimonialValidationTest` | Random integers from -100 to 100 |
| 7: Career insights grouping | `CareerInsightQueryTest` | Sets of 0–60 insights with random types and sort_orders |
| 8: Companies pagination | `CompanyPaginationTest` | Sets of 0–50 companies with random names |
| 9: Placeholder generation | `CompanyLogoTest` | Random company names |
| 10: Company jobs ordering | `CompanyJobsTest` | Companies with 0–20 jobs with random timestamps |
| 11: Search input handling | `SearchInputTest` | Random strings (empty, whitespace, valid) |
| 12: Search filter | `SearchFilterTest` | Random job listings and search terms |
| 13: Duplicate prevention | `JobApplicationTest` | Random user-job pairs with prior applications |
| 14: Home page limits | `HomePageQueryTest` | Random counts of jobs and companies |

### Integration Tests

- Full page load tests for all new routes (200 responses with seeded data)
- Authentication flow for job application (redirect → login → return)
- Database seeder execution end-to-end
- Company-to-job-listing relationship through the full stack

### Browser Tests (Optional)

- Alpine.js filter interactions on companies page
- Logo `onerror` fallback behavior
- Hero search form submission and popular term population
- Chart rendering on insights page
