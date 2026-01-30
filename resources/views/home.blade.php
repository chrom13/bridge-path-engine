@extends('layouts.app')

@section('title', 'Home - Bridge Path Engine')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Find Your Perfect Career Path
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Search for opportunities from Torre.ai and discover how your skills match.
            Get personalized recommendations to bridge any gaps.
        </p>
    </div>

    <!-- Search Form -->
    <div class="max-w-4xl mx-auto mb-16">
        <form action="{{ route('opportunities.search') }}" method="GET" class="relative">
            <div class="flex items-center bg-white rounded-xl shadow-xl border-2 border-gray-200 focus-within:border-indigo-500 transition-all duration-200">
                <div class="pl-6 pr-3">
                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    name="query"
                    placeholder="Search for jobs, skills, or companies (e.g., 'Laravel', 'React', 'Remote')"
                    class="flex-1 py-5 px-2 text-lg border-0 focus:ring-0 focus:outline-none"
                    autofocus
                >
                <button
                    type="submit"
                    class="m-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-150 shadow-md hover:shadow-lg"
                >
                    Search Opportunities
                </button>
            </div>
            <p class="mt-3 text-sm text-gray-500 text-center">
                Press Enter or click "Search Opportunities" to find matching jobs
            </p>
        </form>
    </div>

    <!-- Features Grid -->
    <div class="grid md:grid-cols-3 gap-8 mb-12">
        <!-- Feature 1 -->
        <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-xl transition duration-200">
            <div class="bg-indigo-100 w-14 h-14 rounded-lg flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Skill Gap Analysis</h3>
            <p class="text-gray-600">
                Compare your skills against job requirements with our intelligent matching algorithm.
                Identify critical gaps and growth opportunities.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-xl transition duration-200">
            <div class="bg-green-100 w-14 h-14 rounded-lg flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Personalized Learning</h3>
            <p class="text-gray-600">
                Get AI-powered recommendations for courses and learning resources tailored to your specific skill gaps.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white p-8 rounded-xl shadow-md hover:shadow-xl transition duration-200">
            <div class="bg-purple-100 w-14 h-14 rounded-lg flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Expert Mentors</h3>
            <p class="text-gray-600">
                Connect with industry experts from Torre who can help you develop the skills you need to succeed.
            </p>
        </div>
    </div>

    <!-- Your Top Strengths -->
    @php
        $user = Session::get('user');
        $strengths = $user['genome_data']['strengths'] ?? [];

        // Sort by weight (importance) and get top 5
        usort($strengths, function($a, $b) {
            return ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0);
        });
        $topStrengths = array_slice($strengths, 0, 5);

        // Proficiency colors
        $proficiencyColors = [
            'expert' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-300'],
            'proficient' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-300'],
            'competent' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-300'],
            'beginner' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300'],
        ];
    @endphp

    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white">
        <div class="mb-6">
            <h2 class="text-2xl font-bold mb-2">Your Top Strengths</h2>
            <p class="text-indigo-100">
                Search for opportunities matching your strongest skills
            </p>
        </div>

        @if(count($topStrengths) > 0)
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @foreach($topStrengths as $strength)
                    @php
                        $proficiency = strtolower($strength['proficiency'] ?? 'beginner');
                        $colors = $proficiencyColors[$proficiency] ?? $proficiencyColors['beginner'];
                        $hasRecommendations = ($strength['recommendations'] ?? 0) > 0;
                    @endphp

                    <a href="{{ route('opportunities.search', ['query' => $strength['name']]) }}"
                       class="group bg-white rounded-xl p-4 hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex flex-col h-full">
                            <!-- Skill Name -->
                            <h3 class="text-gray-900 font-semibold text-sm mb-2 group-hover:text-indigo-600 transition-colors">
                                {{ $strength['name'] }}
                            </h3>

                            <!-- Proficiency Badge -->
                            <div class="flex items-center justify-between mt-auto">
                                <span class="{{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }} border px-2 py-1 rounded-md text-xs font-medium capitalize">
                                    {{ $proficiency }}
                                </span>

                                <!-- Recommendations indicator -->
                                @if($hasRecommendations)
                                    <div class="flex items-center text-xs text-gray-500" title="{{ $strength['recommendations'] }} recommendations">
                                        <svg class="h-3 w-3 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="font-medium">{{ $strength['recommendations'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- View All Skills Link -->
            <div class="mt-6 text-center">
                <p class="text-indigo-100 text-sm">
                    Showing your top {{ count($topStrengths) }} skills • {{ count($strengths) }} total skills in your profile
                </p>
            </div>
        @else
            <div class="bg-white/10 backdrop-blur rounded-lg p-6 text-center">
                <p class="text-white/90">No skills found in your Torre profile. Update your profile on Torre.ai to see your strengths here.</p>
            </div>
        @endif
    </div>

    <!-- Stats Section -->
    <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-indigo-600 mb-2">4</div>
            <div class="text-gray-600 text-sm">Demo Opportunities</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-green-600 mb-2">15+</div>
            <div class="text-gray-600 text-sm">Skills Tracked</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-purple-600 mb-2">3</div>
            <div class="text-gray-600 text-sm">Recommendation Tiers</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="text-3xl font-bold text-pink-600 mb-2">100%</div>
            <div class="text-gray-600 text-sm">AI-Powered</div>
        </div>
    </div>
</div>
@endsection
