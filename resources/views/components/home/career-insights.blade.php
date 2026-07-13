@props(['careerInsights'])

@php
    $salaryData = $careerInsights->get('salary', collect());
    $hiringTrends = $careerInsights->get('trend', collect());
    $inDemandSkills = $careerInsights->get('skill', collect());
@endphp

<section aria-labelledby="career-insights-heading">
    <h2 id="career-insights-heading" class="text-2xl font-bold text-foreground mb-2">Career Insights</h2>
    <p class="text-muted mb-8">Data-driven insights to guide your career decisions</p>
    {{-- Charts Section --}}
    <div id="career-insights-charts" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Salary Comparison Bar Chart --}}
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4">Salary Comparison</h3>
            @if($salaryData->isEmpty())
                <x-empty-state message="No salary data available" />
            @else 
                <div class="relative">
                    <canvas
                        id="salaryChart"
                        aria-label="Bar chart comparing average salaries across job roles"
                        role="img"
                        data-salary="{{ json_encode($salaryData->map(fn($item) => ['role' => $item->label, 'salary' => (int) $item->value])->values()) }}"
                    ></canvas>
                    {{-- Fallback content if Chart.js fails --}}
                    <div id="salaryChartFallback" class="hidden">
                        <p class="text-sm text-muted mb-3">Average salaries by role:</p>
                        <ul class="space-y-2">
                            @foreach ($salaryData as $item)
                                <li class="flex justify-between text-sm">
                                    <span class="text-foreground">{{ $item->label }}</span>
                                    <span class="font-medium text-foreground">${{ number_format((int) $item->value) }}</span>
                                </li>
                            @endforeach
                        </ul> 
                    </div>
                </div>
            @endif
        </div>

        {{-- Hiring Trends Line Chart --}}
        <div class="bg-card border border-border rounded-lg p-6">
            <h3 class="text-lg font-semibold text-foreground mb-4">Hiring Trends</h3>
            @if($hiringTrends->isEmpty())
                <x-empty-state message="No hiring trend data available" />
            @else
                <div class="relative">
                    <canvas
                        id="hiringTrendsChart"
                        aria-label="Line chart showing hiring activity trends over the past months"
                        role="img"
                        data-trends="{{ json_encode($hiringTrends->map(fn($item) => ['month' => $item->label, 'value' => (int) $item->value])->values()) }}"
                    ></canvas>
                    {{-- Fallback content if Chart.js fails --}}
                    <div id="hiringTrendsFallback" class="hidden">
                        <p class="text-sm text-muted mb-3">Monthly hiring activity:</p>
                        <ul class="space-y-2">
                            @foreach ($hiringTrends as $item)
                                <li class="flex justify-between text-sm">
                                    <span class="text-foreground">{{ $item->label }}</span>
                                    <span class="font-medium text-foreground">{{ $item->value }} postings</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- In-Demand Skills --}}
    <div class="bg-card border border-border rounded-lg p-6">
        <h3 class="text-lg font-semibold text-foreground mb-4">Top In-Demand Skills</h3>
        @if($inDemandSkills->isEmpty())
            <x-empty-state message="No skills data available" />
        @else
            <div class="space-y-4">
                @foreach ($inDemandSkills as $skill)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-foreground">{{ $skill->label }}</span>
                            <span class="text-sm text-muted">{{ $skill->value }}%</span>
                        </div>
                        <div class="w-full bg-secondary rounded-full h-2.5" role="progressbar" aria-valuenow="{{ (int) $skill->value }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $skill->label }} demand: {{ $skill->value }}%">
                            <div class="bg-primary h-2.5 rounded-full transition-all duration-500" style="width: {{ $skill->value }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const section = document.getElementById('career-insights-charts');
        if (!section) return;

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    observer.disconnect();
                    loadChartJs();
                }
            });
        }, { threshold: 0.1 });

        observer.observe(section);

        function loadChartJs() {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
            script.onload = function () {
                initCharts();
            };
            script.onerror = function () {
                showFallback();
            };
            document.head.appendChild(script);
        }

        function showFallback() {
            const salaryCanvas = document.getElementById('salaryChart');
            const trendsCanvas = document.getElementById('hiringTrendsChart');
            const salaryFallback = document.getElementById('salaryChartFallback');
            const trendsFallback = document.getElementById('hiringTrendsFallback');

            if (salaryCanvas) salaryCanvas.classList.add('hidden');
            if (trendsCanvas) trendsCanvas.classList.add('hidden');
            if (salaryFallback) salaryFallback.classList.remove('hidden');
            if (trendsFallback) trendsFallback.classList.remove('hidden');
        }

        function initCharts() {
            try {
                initSalaryChart();
                initHiringTrendsChart();
            } catch (e) {
                showFallback();
            }
        }

        function initSalaryChart() {
            const canvas = document.getElementById('salaryChart');
            if (!canvas) return;
            const salaryData = JSON.parse(canvas.dataset.salary);

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: salaryData.map(function (item) { return item.role; }),
                    datasets: [{
                        label: 'Average Salary ($)',
                        data: salaryData.map(function (item) { return item.salary; }),
                        backgroundColor: 'rgba(234, 88, 12, 0.7)',
                        borderColor: 'rgba(234, 88, 12, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Job Role',
                                color: '#78716c'
                            },
                            ticks: { color: '#78716c' },
                            grid: { display: false }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Salary ($)',
                                color: '#78716c'
                            },
                            ticks: {
                                color: '#78716c',
                                callback: function (value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function initHiringTrendsChart() {
            const canvas = document.getElementById('hiringTrendsChart');
            if (!canvas) return;
            const trendsData = JSON.parse(canvas.dataset.trends);

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: trendsData.map(function (item) { return item.month; }),
                    datasets: [{
                        label: 'Hiring Activity',
                        data: trendsData.map(function (item) { return item.value; }),
                        borderColor: 'rgba(234, 88, 12, 1)',
                        backgroundColor: 'rgba(234, 88, 12, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(234, 88, 12, 1)',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Month',
                                color: '#78716c'
                            },
                            ticks: { color: '#78716c' },
                            grid: { display: false }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Hiring Activity',
                                color: '#78716c'
                            },
                            ticks: { color: '#78716c' },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    });
</script>
 