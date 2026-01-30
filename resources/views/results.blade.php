@extends('layouts.app')

@section('title', 'Search Results - Bridge Path Engine')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Search Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    @if($query)
                        Search Results for "{{ $query }}"
                    @else
                        All Opportunities
                    @endif
                </h1>
                <p class="text-gray-600 mt-1">
                    Found {{ count($opportunities) }} {{ Str::plural('opportunity', count($opportunities)) }}
                </p>
            </div>
            <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                <svg class="h-5 w-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('opportunities.search') }}" method="GET" class="relative">
            <div class="flex items-center bg-white rounded-lg shadow-md border border-gray-200 focus-within:border-indigo-500 transition-all">
                <div class="pl-4 pr-2">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input
                    type="text"
                    name="query"
                    value="{{ $query }}"
                    placeholder="Refine your search..."
                    class="flex-1 py-3 px-2 border-0 focus:ring-0 focus:outline-none"
                >
                <button
                    type="submit"
                    class="m-1.5 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-md transition duration-150"
                >
                    Search
                </button>
            </div>
        </form>
    </div>

    <!-- Results -->
    @if(count($opportunities) > 0)
        <div class="space-y-6">
            @foreach($opportunities as $opportunity)
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition duration-200 overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start space-x-4 flex-1">
                                <!-- Company Logo -->
                                @if(isset($opportunity['organizations'][0]['picture']))
                                    <img
                                        src="{{ $opportunity['organizations'][0]['picture'] }}"
                                        alt="{{ $opportunity['organizations'][0]['name'] }}"
                                        class="w-16 h-16 rounded-lg object-cover border border-gray-200"
                                    >
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                        {{ substr($opportunity['organizations'][0]['name'], 0, 1) }}
                                    </div>
                                @endif

                                <!-- Job Info -->
                                <div class="flex-1">
                                    <h2 class="text-2xl font-bold text-gray-900 mb-1">
                                        {{ $opportunity['objective'] }}
                                    </h2>
                                    <p class="text-gray-600 mb-2">
                                        {{ $opportunity['organizations'][0]['name'] }}
                                        @if(isset($opportunity['organizations'][0]['size']))
                                            <span class="text-gray-400">• {{ $opportunity['organizations'][0]['size'] }} employees</span>
                                        @endif
                                    </p>
                                    <p class="text-gray-700">
                                        {{ $opportunity['tagline'] }}
                                    </p>
                                </div>
                            </div>

                            <!-- Remote Badge -->
                            @if($opportunity['remote'])
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Remote
                                </span>
                            @endif
                        </div>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 py-4 border-t border-b border-gray-100">
                            <!-- Commitment -->
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Commitment</p>
                                <p class="text-gray-900 font-medium capitalize">{{ str_replace('-', ' ', $opportunity['commitment']) }}</p>
                            </div>

                            <!-- Type -->
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Type</p>
                                <p class="text-gray-900 font-medium capitalize">{{ str_replace('-', ' ', $opportunity['type']) }}</p>
                            </div>

                            <!-- Compensation -->
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Salary</p>
                                @if(isset($opportunity['compensation']['data']))
                                    @php
                                        $comp = $opportunity['compensation']['data'];
                                        $currency = $comp['currency'] ?? 'USD';
                                    @endphp
                                    @if($comp['minAmount'] > 0)
                                        <p class="text-gray-900 font-medium">
                                            {{ number_format($comp['minAmount']) }}
                                            @if(isset($comp['maxAmount']) && $comp['maxAmount'] > 0)
                                                - {{ number_format($comp['maxAmount']) }}
                                            @endif
                                            {{ $currency }}
                                        </p>
                                    @else
                                        <p class="text-gray-900 font-medium">Equity/Stocks</p>
                                    @endif
                                @else
                                    <p class="text-gray-500">Not specified</p>
                                @endif
                            </div>

                            <!-- Location -->
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Location</p>
                                @if($opportunity['remote'] && isset($opportunity['place']['anywhere']) && $opportunity['place']['anywhere'])
                                    <p class="text-gray-900 font-medium">Anywhere</p>
                                @elseif(isset($opportunity['locations']) && count($opportunity['locations']) > 0)
                                    <p class="text-gray-900 font-medium">{{ $opportunity['locations'][0] }}</p>
                                @else
                                    <p class="text-gray-900 font-medium">Remote</p>
                                @endif
                            </div>
                        </div>

                        <!-- Skills -->
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Required Skills:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($opportunity['skills'] as $skill)
                                    <span class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium border border-indigo-200">
                                        {{ $skill['name'] }}
                                        @if(isset($skill['proficiency']))
                                            <span class="text-indigo-500 ml-1">• {{ ucfirst($skill['proficiency']) }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <!-- Additional Compensation -->
                        @if(isset($opportunity['additionalCompensation']) && count($opportunity['additionalCompensation']) > 0)
                            <div class="mb-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Benefits:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($opportunity['additionalCompensation'] as $benefit)
                                        <span class="px-3 py-1 bg-green-50 text-green-700 rounded-lg text-sm font-medium border border-green-200">
                                            {{ ucwords(str_replace('-', ' ', $benefit)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="text-sm text-gray-500">
                                Posted {{ \Carbon\Carbon::parse($opportunity['created'])->diffForHumans() }}
                            </div>
                            <div class="flex space-x-3">
                                <button
                                    onclick="alert('View Details feature coming soon!')"
                                    class="px-6 py-2.5 border border-indigo-600 text-indigo-600 font-medium rounded-lg hover:bg-indigo-50 transition duration-150"
                                >
                                    View Details
                                </button>
                                <form method="POST" action="{{ route('opportunities.apply', $opportunity['id']) }}" class="inline">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-150 shadow-md hover:shadow-lg"
                                    >
                                        Apply Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- No Results -->
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No opportunities found</h3>
            <p class="text-gray-600 mb-6">
                Try adjusting your search query or
                <a href="{{ route('opportunities.search') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">browse all opportunities</a>
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-150">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>
    @endif
</div>
@endsection
