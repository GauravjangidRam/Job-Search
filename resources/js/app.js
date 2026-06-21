import Alpine from 'alpinejs';

// Register Alpine.js data components

// Mobile Navigation Toggle
Alpine.data('mobileMenu', () => ({
    open: false,
    toggle() {
        this.open = !this.open;
    }
}));

// Hero Search
Alpine.data('heroSearch', () => ({
    jobTitle: '',
    location: '',
    error: '',
    setPopularTerm(term) {
        this.jobTitle = term;
        this.error = '';
    },
    submit() {
        const trimmed = this.jobTitle.trim();
        if (!trimmed) {
            this.error = 'Please enter a job title to search';
            return;
        }
        if (trimmed.length > 100) {
            this.error = 'Search term must be 100 characters or less';
            return;
        }
        this.error = '';
        // Navigate to job search results
        const params = new URLSearchParams();
        params.set('search', trimmed);
        if (this.location.trim()) {
            params.set('location', this.location.trim());
        }
        window.location.href = `/jobs?${params.toString()}`;
    }
}));

// Top Navigation Search
Alpine.data('topSearch', () => ({
    open: false,
    query: '',
    toggle() {
        this.open = !this.open;
        if (this.open) {
            this.$nextTick(() => {
                try { this.$refs.input && this.$refs.input.focus(); } catch (e) { /* ignore */ }
            });
        }
    },
    close() { this.open = false; },
    submit() {
        const trimmed = (this.query || '').trim();
        if (!trimmed) return;
        const params = new URLSearchParams();
        params.set('search', trimmed);
        window.location.href = `/jobs?${params.toString()}`;
    }
}));

// Job Discovery Filters
Alpine.data('jobFilters', (jobs = []) => ({
    jobs: jobs,
    filters: {
        jobType: [],
        location: [],
        salary: [],
        postedDate: []
    },
    get activeTags() {
        const tags = [];
        for (const [category, values] of Object.entries(this.filters)) {
            for (const value of values) {
                tags.push({ category, value });
            }
        }
        return tags;
    },
    toggleFilter(category, value) {
        const index = this.filters[category].indexOf(value);
        if (index === -1) {
            this.filters[category].push(value);
        } else {
            this.filters[category].splice(index, 1);
        }
    },
    removeTag(category, value) {
        const index = this.filters[category].indexOf(value);
        if (index !== -1) {
            this.filters[category].splice(index, 1);
        }
    },
    isActive(category, value) {
        return this.filters[category].includes(value);
    },
    get filteredJobs() {
        if (this.activeTags.length === 0) {
            return this.jobs;
        }

        return this.jobs.filter(job => {
            // AND logic across categories: job must match at least one option in each active category
            if (this.filters.jobType.length > 0) {
                const jobTypeNormalized = (job.job_type || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const matchesJobType = this.filters.jobType.some(type => {
                    return type.toLowerCase().replace(/[^a-z0-9]/g, '') === jobTypeNormalized;
                });
                if (!matchesJobType) return false;
            }
            if (this.filters.location.length > 0) {
                const locationTypeNormalized = (job.location_type || '').toLowerCase().replace(/[^a-z0-9]/g, '');
                const matchesLocation = this.filters.location.some(type => {
                    return type.toLowerCase().replace(/[^a-z0-9]/g, '') === locationTypeNormalized;
                });
                if (!matchesLocation) return false;
            }
            if (this.filters.salary.length > 0) {
                const salaryMin = job.salary_min || 0;
                const matchesSalary = this.filters.salary.some(range => {
                    const cleanRange = range.replace('$', '').replace('₹', '').replace('–', '-').replace(/\s/g, '').toLowerCase();
                    if (cleanRange === '0-50k' || cleanRange === '0-50000') return salaryMin < 50000;
                    if (cleanRange === '50k-100k' || cleanRange === '50000-100000') return salaryMin >= 50000 && salaryMin < 100000;
                    if (cleanRange === '100k-150k' || cleanRange === '100000-150000') return salaryMin >= 100000 && salaryMin < 150000;
                    if (cleanRange === '150k+' || cleanRange === '150000+') return salaryMin >= 150000;
                    return false;
                });
                if (!matchesSalary) return false;
            }
            if (this.filters.postedDate.length > 0) {
                const createdAt = new Date(job.created_at);
                const now = new Date();
                const diffDays = Math.floor((now - createdAt) / (1000 * 60 * 60 * 24));
                const matchesDate = this.filters.postedDate.some(range => {
                    if (range === 'Last 24 hours') return diffDays <= 1;
                    if (range === 'Last 7 days') return diffDays <= 7;
                    if (range === 'Last 30 days') return diffDays <= 30;
                    if (range === 'All time') return true;
                    return false;
                });
                if (!matchesDate) return false;
            }
            return true;
        });
    }
}));

// Animated Job Card (Hero)
Alpine.data('animatedJobCard', () => ({
    visible: false,
    init() {
        setTimeout(() => {
            this.visible = true;
        }, 500);
    }
}));

// Make Alpine available on the window for debugging
window.Alpine = Alpine;

// Initialize Alpine.js
Alpine.start();
