@extends('layouts.app')

@section('content')
    <x-home.navigation-bar />
    <div class="max-w-[1400px] mx-auto pt-16">
        <!-- Page Header -->
        <section aria-label="Career Insights Header" class="py-16 px-6 md:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-4">Career Insights</h1>
            <p class="text-lg text-muted max-w-2xl mx-auto">Explore salary data, hiring trends, and in-demand skills to guide your career decisions.</p>
        </section> 
        <!-- Salary Data Section -->
        <section aria-label="Salary Data" class="py-12 px-6 md:px-8">
            <h2 class="text-2xl font-bold text-foreground mb-8">Salary Data</h2>
            @if($insights['salary']->isEmpty())
                <x-empty-state message="No data is currently available for this section." />
            @else
                {{-- CSS Bar Chart --}}
                <div class="bg-card border border-border rounded-card p-6" role="img" aria-label="Bar chart showing salary data by role">
                    <div class="space-y-4">
                        @php 
                            $salaryData = $insights['salary']->take(10);
                            $maxSalary = $salaryData->max('value') ?: 1;
                        @endphp
                        @foreach($salaryData as $insight)
                            @php
                                $percentage = ($insight->value / $maxSalary) * 100;
                            @endphp
                            <div class="flex items-center gap-4">
                                <span class="text-sm text-foreground font-medium w-40 shrink-0 truncate" title="{{ $insight->label }}">{{ $insight->label }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                    <div class="bg-primary h-full rounded-full flex items-center justify-end pr-3 transition-all duration-300" style="width: {{ $percentage }}%">
                                        <span class="text-xs font-semibold text-white whitespace-nowrap">${{ number_format((float) $insight->value, 0, '.', ',') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div> 
                {{-- Accessible Fallback Table --}}
                <noscript>
                    <table class="w-full mt-4 border-collapse bg-card border border-border rounded-card">
                        <caption class="sr-only">Salary data by role</caption>
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left p-3 text-sm font-semibold text-foreground" scope="col">Role</th>
                                <th class="text-right p-3 text-sm font-semibold text-foreground" scope="col">Annual Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryData as $insight)
                                <tr class="border-b border-border last:border-b-0">
                                    <td class="p-3 text-sm text-foreground">{{ $insight->label }}</td>
                                    <td class="p-3 text-sm text-foreground text-right">${{ number_format((float) $insight->value, 0, '.', ',') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </noscript>
                {{-- Screen reader accessible data --}}
                <div class="sr-only">
                    <h3>Salary data table</h3>
                    <dl>
                        @foreach($salaryData as $insight)
                            <dt>{{ $insight->label }}</dt>
                            <dd>${{ number_format((float) $insight->value, 0, '.', ',') }}</dd>
                        @endforeach
                    </dl>
                </div>
            @endif
        </section>
        <!-- Hiring Trends Section -->
        <section aria-label="Hiring Trends" class="py-12 px-6 md:px-8">
            <h2 class="text-2xl font-bold text-foreground mb-8">Hiring Trends</h2>
            @if($insights['trend']->isEmpty())
                <x-empty-state message="No data is currently available for this section." />
            @else
                {{-- CSS Line Chart --}}
                @php
                    $trendData = $insights['trend']->take(12);
                    $maxTrend = $trendData->max('value') ?: 1;
                    $minTrend = $trendData->min('value') ?: 0;
                    $range = $maxTrend - $minTrend ?: 1;
                @endphp
                <div class="bg-card border border-border rounded-card p-6" role="img" aria-label="Line chart showing hiring trends over time">
                    {{-- Chart area with connected points --}}
                    <div class="relative h-64 flex items-end gap-1 justify-between">
                        @foreach($trendData as $index => $insight)
                            @php
                                $heightPercent = (($insight->value - $minTrend) / $range) * 80 + 10;
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full relative">
                                {{-- Value label --}}
                                <span class="text-xs font-medium text-foreground mb-1">{{ number_format((float) $insight->value, 0) }}</span>
                                {{-- Data point bar --}}
                                <div class="w-full max-w-[40px] bg-primary/20 border-t-4 border-primary rounded-t transition-all duration-300" style="height: {{ $heightPercent }}%"></div>
                            </div>
                        @endforeach
                    </div>
                    {{-- X-axis labels --}}
                    <div class="flex justify-between mt-3 border-t border-border pt-3">
                        @foreach($trendData as $insight)
                            <div class="flex-1 text-center">
                                <span class="text-xs text-muted truncate block">{{ $insight->label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- Accessible Fallback Table --}}
                <noscript>
                    <table class="w-full mt-4 border-collapse bg-card border border-border rounded-card">
                        <caption class="sr-only">Hiring trends by month</caption>
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left p-3 text-sm font-semibold text-foreground" scope="col">Month</th>
                                <th class="text-right p-3 text-sm font-semibold text-foreground" scope="col">Job Postings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trendData as $insight)
                                <tr class="border-b border-border last:border-b-0">
                                    <td class="p-3 text-sm text-foreground">{{ $insight->label }}</td>
                                    <td class="p-3 text-sm text-foreground text-right">{{ number_format((float) $insight->value, 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </noscript>
                {{-- Screen reader accessible data --}}
                <div class="sr-only">
                    <h3>Hiring trends data</h3>
                    <dl>
                        @foreach($trendData as $insight)
                            <dt>{{ $insight->label }}</dt>
                            <dd>{{ number_format((float) $insight->value, 0) }} job postings</dd>
                        @endforeach
                    </dl>
                </div>
            @endif
        </section>
        <!-- In-Demand Skills Section -->
        <section aria-label="In-Demand Skills" class="py-12 px-6 md:px-8 mb-16">
            <h2 class="text-2xl font-bold text-foreground mb-8">In-Demand Skills</h2>
            @if($insights['skill']->isEmpty())
                <x-empty-state message="No data is currently available for this section." />
            @else
                <div class="bg-card border border-border rounded-card p-6">
                    <div class="space-y-5">
                        @php
                            $skillData = $insights['skill']->take(10);
                        @endphp
                        @foreach($skillData as $insight)
                            @php
                                $percentage = min(100, max(0, (float) $insight->value));
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-foreground">{{ $insight->label }}</span>
                                    <span class="text-sm font-semibold text-foreground">{{ number_format($percentage, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden" role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $insight->label }} demand: {{ number_format($percentage, 0) }}%">
                                    <div class="bg-primary h-full rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {{-- Accessible Fallback Table --}}
                <noscript>
                    <table class="w-full mt-4 border-collapse bg-card border border-border rounded-card">
                        <caption class="sr-only">In-demand skills with percentage</caption>
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left p-3 text-sm font-semibold text-foreground" scope="col">Skill</th>
                                <th class="text-right p-3 text-sm font-semibold text-foreground" scope="col">Demand (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($skillData as $insight)
                                <tr class="border-b border-border last:border-b-0">
                                    <td class="p-3 text-sm text-foreground">{{ $insight->label }}</td>
                                    <td class="p-3 text-sm text-foreground text-right">{{ number_format(min(100, max(0, (float) $insight->value)), 0) }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </noscript>
            @endif
        </section>
    </div>
@endsection