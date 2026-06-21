@extends('layouts.app')

@section('title', 'Career Insights & Salary Data')

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
                <div id="salary-chart" class="min-h-[350px]"></div>
                
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
                            @foreach($insights['salary']->take(10) as $insight)
                                <tr class="border-b border-border last:border-b-0">
                                    <td class="p-3 text-sm text-foreground">{{ $insight->label }}</td>
                                    <td class="p-3 text-sm text-foreground text-right">₹{{ number_format((float) $insight->value, 0, '.', ',') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </noscript>
                {{-- Screen reader accessible data --}}
                <div class="sr-only">
                    <h3>Salary data table</h3>
                    <dl>
                        @foreach($insights['salary']->take(10) as $insight)
                            <dt>{{ $insight->label }}</dt>
                            <dd>₹{{ number_format((float) $insight->value, 0, '.', ',') }}</dd>
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
                <div id="trends-chart" class="min-h-[350px]"></div>

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
                            @foreach($insights['trend']->take(12) as $insight)
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
                        @foreach($insights['trend']->take(12) as $insight)
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
                <div id="skills-chart" class="min-h-[350px]"></div>

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
                            @foreach($insights['skill']->take(10) as $insight)
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

    <!-- ApexCharts CDN and Initialization Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Salary Chart
        const salaryData = @json($insights['salary']->take(10));
        if (salaryData.length > 0) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: {
                    mode: 'light'
                },
                series: [{
                    name: 'Average Salary (INR)',
                    data: salaryData.map(item => Number(item.value))
                }],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        barHeight: '60%',
                        distributed: true
                    }
                },
                colors: ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#06b6d4', '#0891b2', '#0e7490'],
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return '₹' + val.toLocaleString('en-IN');
                    },
                    style: {
                        colors: ['#fff'],
                        fontWeight: 600
                    }
                },
                xaxis: {
                    categories: salaryData.map(item => item.label),
                    labels: {
                        formatter: function (val) {
                            if (val >= 100000) {
                                return '₹' + (val / 100000) + 'L';
                            }
                            return '₹' + (val / 1000) + 'k';
                        }
                    }
                },
                grid: {
                    borderColor: 'var(--border, #f3f4f6)'
                },
                legend: { show: false }
            };
            new ApexCharts(document.querySelector("#salary-chart"), options).render();
        }

        // 2. Hiring Trends Chart
        const trendData = @json($insights['trend']->take(12));
        if (trendData.length > 0) {
            const options = {
                chart: {
                    type: 'area',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                series: [{
                    name: 'Job Postings',
                    data: trendData.map(item => Number(item.value))
                }],
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#4f46e5']
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: trendData.map(item => item.label)
                },
                colors: ['#4f46e5'],
                grid: {
                    borderColor: 'var(--border, #f3f4f6)'
                }
            };
            new ApexCharts(document.querySelector("#trends-chart"), options).render();
        }

        // 3. Skills Chart
        const skillData = @json($insights['skill']->take(10));
        if (skillData.length > 0) {
            const options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                series: [{
                    name: 'Demand Percentage',
                    data: skillData.map(item => Number(item.value))
                }],
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        borderRadius: 6
                    }
                },
                colors: ['#06b6d4'],
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val + '%';
                    }
                },
                xaxis: {
                    categories: skillData.map(item => item.label)
                },
                yaxis: {
                    max: 100,
                    labels: {
                        formatter: function (val) {
                            return val + '%';
                        }
                    }
                },
                grid: {
                    borderColor: 'var(--border, #f3f4f6)'
                }
            };
            new ApexCharts(document.querySelector("#skills-chart"), options).render();
        }
    });
    </script>
@endsection