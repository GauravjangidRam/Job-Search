@extends('layouts.app')

@section('title', $job->title . ' at ' . $job->company_name)

@section('meta')
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 150) }}">
    
    <!-- OpenGraph Tags -->
    <meta property="og:title" content="{{ $job->title }} at {{ $job->company_name }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 150) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($job->company_logo_url)
        <meta property="og:image" content="{{ $job->company_logo_url }}">
    @endif

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "JobPosting",
      "title": "{{ $job->title }}",
      "description": "{!! addslashes(e(strip_tags($job->description))) !!}",
      "datePosted": "{{ $job->created_at->toIso8601String() }}",
      "validThrough": "{{ $job->created_at->addDays(60)->toIso8601String() }}",
      "employmentType": "{{ str_replace('-', '_', strtoupper($job->job_type)) }}",
      "hiringOrganization": {
        "@type": "Organization",
        "name": "{{ $job->company_name }}"
        @if($job->company_logo_url)
        ,"logo": "{{ $job->company_logo_url }}"
        @endif
      },
      "jobLocation": {
        "@type": "Place",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "{{ $job->location }}"
        }
      },
      "baseSalary": {
        "@type": "MonetaryAmount",
        "currency": "{{ $job->currency ?? 'INR' }}",
        "value": {
          "@type": "QuantitativeValue",
          "minValue": {{ $job->salary_min }},
          "maxValue": {{ $job->salary_max }},
          "unitText": "YEAR"
        }
      }
    }
    </script>
@endsection

@section('content')
    <x-home.navigation-bar />

    <div class="max-w-[1400px] mx-auto pt-16">
        <div class="py-12 px-6 md:px-8">
            {{-- Back to Jobs Link --}}
            <a href="/jobs" class="inline-flex items-center text-muted hover:text-primary font-medium transition-colors duration-200 mb-8 focus:outline-2 focus:outline-offset-2 focus:outline-primary rounded">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Jobs
            </a>

            <div class="bg-card border border-border rounded-[var(--radius-card)] shadow-sm overflow-hidden">
                {{-- Header Section --}}
                <div class="p-6 md:p-8 border-b border-border">
                    <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
                        {{-- Company Logo --}}
                        @if($job->company_logo_url)
                            <img
                                src="{{ $job->company_logo_url }}"
                                alt="{{ $job->company_name }} logo"
                                class="w-16 h-16 rounded-[var(--radius-card)] object-cover border border-border flex-shrink-0"
                            >
                        @else
                            <div class="w-16 h-16 rounded-[var(--radius-card)] bg-primary flex items-center justify-center flex-shrink-0" aria-hidden="true">
                                <span class="text-white text-2xl font-bold">{{ strtoupper(substr($job->company_name, 0, 1)) }}</span>
                            </div>
                        @endif

                        {{-- Title and Company --}}
                        <div class="flex-1 min-w-0">
                            <h1 class="text-2xl md:text-3xl font-bold text-foreground">{{ $job->title }}</h1>
                            <p class="text-lg text-muted mt-1">{{ $job->company_name }}</p>
                        </div>
                    </div>
                </div>

                {{-- Job Meta Information --}}
                <div class="p-6 md:p-8 border-b border-border">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Location --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="map-pin" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Location</p>
                                <p class="font-medium text-foreground">{{ $job->location }}</p>
                            </div>
                        </div>

                        {{-- Salary Range --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                @if(($job->currency ?? 'INR') === 'USD')
                                    <i data-lucide="dollar-sign" class="w-5 h-5 text-primary"></i>
                                @else
                                    <i data-lucide="indian-rupee" class="w-5 h-5 text-primary"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-muted">Salary</p>
                                <p class="font-medium text-foreground">{{ $job->currency_symbol }}{{ number_format($job->salary_min) }} - {{ $job->currency_symbol }}{{ number_format($job->salary_max) }}</p>
                            </div>
                        </div>

                        {{-- Job Type --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="briefcase" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Job Type</p>
                                <p class="font-medium text-foreground">{{ $job->job_type }}</p>
                            </div>
                        </div>

                        {{-- Location Type --}}
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-secondary rounded-md">
                                <i data-lucide="monitor" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-sm text-muted">Work Mode</p>
                                <p class="font-medium text-foreground">{{ $job->location_type }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Posted Date --}}
                <div class="px-6 md:px-8 py-4 border-b border-border bg-secondary/50">
                    <p class="text-sm text-muted">
                        <i data-lucide="clock" class="w-4 h-4 inline-block mr-1 align-text-bottom"></i>
                        Posted {{ $job->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Description --}}
                <div class="p-6 md:p-8 border-b border-border">
                    <h2 class="text-xl font-semibold text-foreground mb-4">Job Description</h2>
                    <div class="prose prose-stone max-w-none text-foreground/90 leading-relaxed whitespace-pre-line">{{ $job->description }}</div>
                </div>

                {{-- Skills --}}
                @if(!empty($job->skills))
                    <div class="p-6 md:p-8 border-b border-border">
                        <h2 class="text-xl font-semibold text-foreground mb-4">Required Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->skills as $skill)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-primary/10 text-primary border border-primary/20">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Apply Now & Save Buttons --}}
                <div class="p-6 md:p-8 flex flex-wrap items-center gap-4">
                    <a href="{{ route('jobs.apply', $job->hashed_id) }}" class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-[var(--radius-card)] hover:bg-primary-light transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                        Apply Now
                    </a>

                    @auth
                        @if(auth()->user()->isSeeker())
                            <div x-data="{
                                bookmarked: {{ auth()->user()->bookmarks()->where('job_listing_id', $job->id)->exists() ? 'true' : 'false' }},
                                loading: false,
                                async toggle() {
                                    if (this.loading) return;
                                    this.loading = true;
                                    try {
                                        let response = await fetch('{{ route('bookmarks.toggle', $job->hashed_id) }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        });
                                        if (response.ok) {
                                            let data = await response.json();
                                            this.bookmarked = data.bookmarked;
                                        }
                                    } catch (e) {
                                        console.error('Bookmark error:', e);
                                    } finally {
                                        this.loading = false;
                                    }
                                }
                            }">
                                <button
                                    type="button"
                                    @click="toggle()"
                                    :class="bookmarked ? 'bg-primary/10 text-primary border-primary/20' : 'border-border text-muted hover:text-foreground'"
                                    class="inline-flex items-center px-6 py-3 border font-medium rounded-[var(--radius-card)] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                >
                                    <svg class="w-5 h-5 mr-2" :fill="bookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    <span x-text="bookmarked ? 'Saved' : 'Save Job'">Save Job</span>
                                </button>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
@endsection
