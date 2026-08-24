@props(['featuredJobs'])

<div
    x-data="jobFilters({{ Js::from($featuredJobs) }})"
    class="space-y-8 bg-card border border-border/80 rounded-2xl p-6 md:p-8 shadow-sm"
    aria-labelledby="job-discovery-heading"
    role="region"
>  
    {{-- Section Heading --}}
    <div class="text-center max-w-xl mx-auto mb-8">
        <h2 id="job-discovery-heading" class="text-2xl md:text-3xl font-extrabold text-foreground tracking-tight">Discover Jobs</h2>
        <p class="text-muted text-sm mt-1">Filter opportunities by your preferences</p>
    </div>
    {{-- Filter Categories --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Job Type --}} 
        <div>
            <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">Job Type</h3>
            <div class="flex flex-wrap gap-2"> 
                <template x-for="option in ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('jobType', option)"
                        :class="isActive('jobType', option)
                            ? 'bg-primary text-white shadow-xs'
                            : 'bg-secondary text-foreground/80 hover:bg-border/60'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:scale-95"
                        :aria-pressed="isActive('jobType', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div> 
        </div>
        {{-- Location --}}
        <div>
            <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">Location</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['Remote', 'Hybrid', 'On-site']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('location', option)"
                        :class="isActive('location', option)
                            ? 'bg-primary text-white shadow-xs'
                            : 'bg-secondary text-foreground/80 hover:bg-border/60'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:scale-95"
                        :aria-pressed="isActive('location', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Salary Range --}}
        <div>
            <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">Salary Range</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['₹0–₹50k', '₹50k–₹100k', '₹100k–₹150k', '₹150k+']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('salary', option)"
                        :class="isActive('salary', option)
                            ? 'bg-primary text-white shadow-xs'
                            : 'bg-secondary text-foreground/80 hover:bg-border/60'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:scale-95"
                        :aria-pressed="isActive('salary', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>

        {{-- Posted Date --}}
        <div>
            <h3 class="text-xs font-bold text-foreground uppercase tracking-wider mb-3">Posted Date</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="option in ['Last 24 hours', 'Last 7 days', 'Last 30 days', 'All time']" :key="option">
                    <button
                        type="button"
                        @click="toggleFilter('postedDate', option)"
                        :class="isActive('postedDate', option)
                            ? 'bg-primary text-white shadow-xs'
                            : 'bg-secondary text-foreground/80 hover:bg-border/60'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 active:scale-95"
                        :aria-pressed="isActive('postedDate', option).toString()"
                        x-text="option"
                    ></button>
                </template>
            </div>
        </div>
    </div>

    {{-- Active Filter Tags --}}
    <div x-show="activeTags.length > 0" x-cloak class="flex flex-wrap gap-2 pt-4 border-t border-border/70">
        <template x-for="tag in activeTags.slice(0, 12)" :key="tag.category + '-' + tag.value">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold bg-primary/10 text-primary rounded-full border border-primary/20">
                <span x-text="tag.value"></span>
                <button
                    type="button"
                    @click="removeTag(tag.category, tag.value)"
                    class="ml-0.5 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-primary hover:text-white transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-primary"
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
            <p class="text-base font-semibold text-foreground">No results found</p>
            <p class="text-muted text-xs mt-1">Try adjusting your filters to find more opportunities</p>
        </div>

        {{-- Filtered Job Cards --}}
        <div x-show="filteredJobs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <template x-for="job in filteredJobs" :key="job.id">
                <a :href="job.url" class="block bg-card border border-border/80 rounded-2xl p-5 hover:border-primary/40 hover:shadow-md transition-all duration-200 group hover:-translate-y-0.5">
                    <div class="flex items-start gap-3">
                        <img
                            :src="job.company_logo_url || ''"
                            :alt="job.company_name + ' logo'"
                            class="w-10 h-10 rounded-xl object-contain bg-background border border-border/60 p-1"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <div class="w-10 h-10 rounded-xl bg-primary/10 items-center justify-center text-primary hidden" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-foreground truncate group-hover:text-primary transition-colors" x-text="job.title"></h4>
                            <p class="text-xs text-muted font-medium" x-text="job.company_name"></p>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <p class="text-sm font-bold text-foreground" x-text="(job.currency === 'USD' ? '$' : '₹') + Number(job.salary_min).toLocaleString() + ' - ' + (job.currency === 'USD' ? '$' : '₹') + Number(job.salary_max).toLocaleString()"></p>
                        <p class="text-xs text-muted" x-text="job.location"></p>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        <template x-for="skill in (job.skills || []).slice(0, 3)" :key="skill">
                            <span class="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded-full font-medium" x-text="skill"></span>
                        </template>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>
