@extends('layouts.app')

@section('title', 'Career Analysis - Bridge Path Engine')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Back Navigation -->
    <div class="mb-6">
        <a href="{{ route('opportunities.search') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
            <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Search Results
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-8 mb-8">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Career Match Analysis
                </h1>
                <h2 class="text-xl text-gray-700 mb-4">
                    {{ $opportunity['objective'] ?? 'Position Analysis' }}
                </h2>
                <p class="text-gray-600">
                    @if(isset($opportunity['organizations'][0]['name']))
                        {{ $opportunity['organizations'][0]['name'] }}
                    @endif
                </p>
            </div>
            <div class="ml-4">
                @if(isset($opportunity['organizations'][0]['picture']))
                    <img
                        src="{{ $opportunity['organizations'][0]['picture'] }}"
                        alt="{{ $opportunity['organizations'][0]['name'] ?? 'Company' }}"
                        class="w-20 h-20 rounded-lg object-cover border border-gray-200"
                    >
                @endif
            </div>
        </div>
    </div>

    <!-- Analysis Summary -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-xl p-8 mb-8 text-white">
        <div class="flex items-start">
            <div class="bg-white/10 backdrop-blur rounded-lg p-3 mr-4">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-2xl font-bold mb-3">Analysis Summary</h3>
                <p class="text-indigo-100 text-lg leading-relaxed">
                    {{ $analysis->analysisSummary }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-8 mb-8">
        <!-- Radar Chart -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Skills Comparison</h3>
            <div class="relative" style="height: 400px;">
                <canvas id="skillsRadarChart"></canvas>
            </div>
            <div class="mt-6 flex items-center justify-center space-x-6">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-indigo-600 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Your Skills</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-purple-600 rounded mr-2"></div>
                    <span class="text-sm text-gray-600">Job Requirements</span>
                </div>
            </div>
        </div>

        <!-- Skill Gaps -->
        <div class="bg-white rounded-xl shadow-md p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">Priority Skill Gaps</h3>
            <div class="space-y-4">
                @foreach($analysis->skillGaps as $gap)
                    @php
                        $severityColors = [
                            'Critical' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-300'],
                            'Moderate' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-300'],
                            'Minor' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300'],
                        ];
                        $colors = $severityColors[$gap->severity] ?? $severityColors['Moderate'];
                    @endphp
                    <div class="border-l-4 {{ $colors['border'] }} bg-gray-50 p-4 rounded-r-lg">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-bold text-gray-900 text-lg">{{ $gap->skill }}</h4>
                            <span class="px-3 py-1 {{ $colors['bg'] }} {{ $colors['text'] }} text-xs font-semibold rounded-full">
                                {{ $gap->severity }}
                            </span>
                        </div>
                        <p class="text-gray-700 text-sm leading-relaxed">
                            {{ $gap->reason }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500">
                            Priority: #{{ $gap->priority }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Mentor Recommendations -->
    <div class="bg-white rounded-xl shadow-md p-8 mb-8">
        <div class="mb-6">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Recommended Mentors</h3>
            <p class="text-gray-600">Connect with expert mentors who can help you close these skill gaps</p>
        </div>

        <div class="grid md:grid-cols-{{ min(count($analysis->mentorRecommendations), 3) }} gap-6 mb-8">
            @foreach($analysis->mentorRecommendations as $mentor)
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border-2 border-indigo-200 hover:border-indigo-400 transition-all duration-200 hover:shadow-lg">
                    <!-- Mentor Avatar -->
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-2xl mr-4">
                            {{ substr($mentor->mentorName, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 text-lg">{{ $mentor->mentorName }}</h4>
                            <p class="text-sm text-indigo-600 font-medium">{{ $mentor->expertise }}</p>
                        </div>
                    </div>

                    <!-- Why Recommended -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-700 leading-relaxed">
                            {{ $mentor->why }}
                        </p>
                    </div>

                    <!-- Focus Areas -->
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-700 mb-2">Focus Areas:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($mentor->focusAreas as $area)
                                <span class="px-2 py-1 bg-white text-indigo-700 rounded-md text-xs font-medium border border-indigo-200">
                                    {{ $area }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Book Session Button -->
                    <button
                        onclick="alert('Mentorship booking coming soon!')"
                        class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-150"
                    >
                        Book Session
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Upsell Message -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl p-6 text-white">
            <div class="flex items-start">
                <div class="bg-white/10 backdrop-blur rounded-lg p-3 mr-4">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-xl font-bold mb-2">Unlock Unlimited Growth</h4>
                    <p class="text-purple-100 mb-4">
                        {{ $analysis->upsellMessage }}
                    </p>
                    <button
                        onclick="alert('Premium subscription coming soon!')"
                        class="px-6 py-3 bg-white text-purple-600 font-bold rounded-lg hover:bg-purple-50 transition duration-150 shadow-lg"
                    >
                        Upgrade to Premium - $29/mo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-center space-x-4">
        <a
            href="{{ route('opportunities.search') }}"
            class="px-8 py-3 border-2 border-indigo-600 text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition duration-150"
        >
            Find More Opportunities
        </a>
        <form method="POST" action="{{ route('opportunities.apply', $opportunity['id']) }}" class="inline">
            @csrf
            <button
                type="submit"
                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-150 shadow-md hover:shadow-lg"
            >
                Apply to This Position
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('skillsRadarChart');

        const radarData = @json($analysis->radarChartData->toArray());

        new Chart(ctx, {
            type: 'radar',
            data: {
                labels: radarData.labels,
                datasets: [
                    {
                        label: 'Your Skills',
                        data: radarData.user_scores,
                        fill: true,
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        borderColor: 'rgb(79, 70, 229)',
                        pointBackgroundColor: 'rgb(79, 70, 229)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(79, 70, 229)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Job Requirements',
                        data: radarData.job_requirements,
                        fill: true,
                        backgroundColor: 'rgba(147, 51, 234, 0.2)',
                        borderColor: 'rgb(147, 51, 234)',
                        pointBackgroundColor: 'rgb(147, 51, 234)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(147, 51, 234)',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            font: {
                                size: 11
                            }
                        },
                        pointLabels: {
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.r + '%';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
