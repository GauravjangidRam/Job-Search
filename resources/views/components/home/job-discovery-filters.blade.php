@props(['featuredJobs'])

<div
    x-data="jobFilters({{ Js::from($featuredJobs) }})"
    class="space-y-8"
    aria-labelledby="job-discovery-heading"
    role="region"
>  
    {{-- Section Heading --}}
    <div class="text-center mb-8">
        <h2 id="job-discovery-heading" class="text-3xl font-bold text-foreground">Discover Jobs</h2>
        <p class="text-muted mt-2">Filter opportunities by your preferences</p>
    </div>
    {{-- Filter Categories --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Job Type --}}
        <div>
            <h3 class="text-sm font-semibold text-foreground mb-3">Job Type</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('jobType', option)"
                        :class="isActive('jobType', option)
                            ? 'bg-primary text-white'
                            : 'bg-secondary text-foreground hover:bg-border'"
                        class="px-3 py-1.5 text-sm font-medium rounded-card transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        :aria-pressed="isActive('jobType', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>
        {{-- Location --}}
        <div>
            <h3 class="text-sm font-semibold text-foreground mb-3">Location</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['Remote', 'Hybrid', 'On-site']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('location', option)"
                        :class="isActive('location', option)
                            ? 'bg-primary text-white'
                            : 'bg-secondary text-foreground hover:bg-border'"
                        class="px-3 py-1.5 text-sm font-medium rounded-card transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        :aria-pressed="isActive('location', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Salary Range --}}
        <div>
            <h3 class="text-sm font-semibold text-foreground mb-3">Salary Range</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['₹0–₹50k', '₹50k–₹100k', '₹100k–₹150k', '₹150k+']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('salary', option)"
                        :class="isActive('salary', option)
                            ? 'bg-primary text-white'
                            : 'bg-secondary text-foreground hover:bg-border'"
                        class="px-3 py-1.5 text-sm font-medium rounded-card transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        :aria-pressed="isActive('salary', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Posted Date --}}
        <div>
            <h3 class="text-sm font-semibold text-foreground mb-3">Posted Date</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['Last 24 hours', 'Last 7 days', 'Last 30 days', 'All time']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('postedDate', option)"
                        :class="isActive('postedDate', option)
                            ? 'bg-primary text-white'
                            : 'bg-secondary text-foreground hover:bg-border'"
                        class="px-3 py-1.5 text-sm font-medium rounded-card transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        :aria-pressed="isActive('postedDate', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>
    </div>

    {{-- Active Filter Tags --}}
    <div x-show="activeTags.length > 0" x-cloak class="flex flex-wrap gap-2 pt-4 border-t border-border">
        <template x-for="tag in activeTags.slice(0, 12)" :key="tag.category + '-' + tag.value">
            <span class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium bg-accent text-foreground rounded-full">
                <span x-text="tag.value"></span>
                <button
                    type="button"
                    @click="removeTag(tag.category, tag.value)"
                    class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary hover:text-white transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary"
                    :aria-label="'Remove ' + tag.value + ' filter'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </span>
        </template>
    </div>

    {{-- Filtered Job Results --}}
    <div x-show="activeTags.length > 0" x-cloak>
        {{-- No Results Message --}}
        <div x-show="filteredJobs.length === 0" class="text-center py-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto text-muted mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="11" x2="14" y2="11"></line>
            </svg>
            <p class="text-lg font-medium text-foreground">No results found</p>
            <p class="text-muted mt-1">Try adjusting your filters to find more opportunities</p>
        </div>

        {{-- Filtered Job Cards --}}
        <div x-show="filteredJobs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <template x-for="job in filteredJobs" :key="job.id">
                <div class="bg-card border border-border rounded-card p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-start gap-3">
                        <img
                            :src="job.company_logo_url || ''"
                            :alt="job.company_name + ' logo'"
                            class="w-10 h-10 rounded-card object-contain bg-secondary"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div class="w-10 h-10 rounded-card bg-secondary items-center justify-center text-muted hidden" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-foreground truncate" x-text="job.title"></h4>
                            <p class="text-xs text-muted" x-text="job.company_name"></p>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <p class="text-sm font-medium text-foreground" x-text="(job.currency === 'USD' ? '$' : '₹') + Number(job.salary_min).toLocaleString() + ' - ' + (job.currency === 'USD' ? '$' : '₹') + Number(job.salary_max).toLocaleString()"></p>
                        <p class="text-xs text-muted" x-text="job.location"></p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        <template x-for="skill in (job.skills || []).slice(0, 3)" :key="skill">
                            <span class="text-xs bg-secondary text-muted px-2 py-0.5 rounded-full" x-text="skill"></span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
