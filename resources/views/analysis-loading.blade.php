@extends('layouts.app')

@section('title', 'Analyzing Opportunity - Bridge Path Engine')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Loading Card -->
    <div class="bg-white rounded-xl shadow-xl p-12">
        <!-- Animated Icon -->
        <div class="flex justify-center mb-8">
            <div class="relative">
                <!-- Spinning outer ring -->
                <div class="animate-spin rounded-full h-32 w-32 border-t-4 border-b-4 border-indigo-600"></div>
                <!-- Static inner icon -->
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <svg class="h-16 w-16 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Title and Description -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                Analyzing Your Skills Match
            </h1>
            <p class="text-lg text-gray-600 mb-2" id="statusMessage">
                Our AI is analyzing your skills against this opportunity...
            </p>
            <p class="text-sm text-gray-500">
                This may take up to 30 seconds
            </p>
        </div>

        <!-- Progress Steps -->
        <div class="max-w-2xl mx-auto mb-8">
            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="flex items-center" id="step1">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-green-500 text-white flex-shrink-0">
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">Fetching opportunity details</p>
                        <p class="text-xs text-gray-500">Retrieved job requirements and skills</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-center" id="step2">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full border-2 border-indigo-600 flex-shrink-0">
                        <div class="animate-spin h-5 w-5 border-2 border-indigo-600 border-t-transparent rounded-full"></div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900">AI skill gap analysis</p>
                        <p class="text-xs text-gray-500">Comparing your skills with job requirements</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-center" id="step3">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-gray-200 flex-shrink-0">
                        <span class="text-gray-500 font-bold">3</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Generating recommendations</p>
                        <p class="text-xs text-gray-400">Finding the best mentors for you</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-indigo-700">
                        <strong>What's happening:</strong> Our AI is performing a deep analysis of your Torre profile against this opportunity,
                        identifying skill gaps, and matching you with expert mentors who can help you grow.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const analysisId = '{{ $analysisId }}';
    const statusUrl = '{{ route('opportunities.analysis-status', $analysisId) }}';
    let pollInterval;
    let pollCount = 0;
    const maxPolls = 120; // 2 minutes max (2 seconds * 120)

    // Status messages
    const statusMessages = [
        'Analyzing your skill profile...',
        'Comparing with job requirements...',
        'Calculating match scores...',
        'Identifying skill gaps...',
        'Finding the best mentors for you...',
        'Almost done, generating your personalized roadmap...',
    ];

    let messageIndex = 0;

    // Update status message periodically
    function rotateStatusMessage() {
        const messageElement = document.getElementById('statusMessage');
        messageElement.style.opacity = '0';

        setTimeout(() => {
            messageElement.textContent = statusMessages[messageIndex];
            messageElement.style.opacity = '1';
            messageIndex = (messageIndex + 1) % statusMessages.length;
        }, 300);
    }

    // Rotate messages every 4 seconds
    setInterval(rotateStatusMessage, 4000);

    // Poll for analysis status
    function pollStatus() {
        fetch(statusUrl)
            .then(response => response.json())
            .then(data => {
                console.log('Analysis status:', data.status);

                if (data.status === 'completed') {
                    clearInterval(pollInterval);
                    updateStep2Completed();
                    updateStep3Completed();

                    // Show success message
                    document.getElementById('statusMessage').textContent = 'Analysis complete! Redirecting...';

                    // Redirect to results after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else if (data.status === 'failed') {
                    clearInterval(pollInterval);
                    showError(data.error_message || 'Analysis failed. Please try again.');
                } else if (data.status === 'processing') {
                    updateStep2Processing();
                }

                pollCount++;

                // Stop polling after max attempts
                if (pollCount >= maxPolls) {
                    clearInterval(pollInterval);
                    showError('Analysis is taking longer than expected. Please refresh the page or try again.');
                }
            })
            .catch(error => {
                console.error('Polling error:', error);
                pollCount++;

                if (pollCount >= maxPolls) {
                    clearInterval(pollInterval);
                    showError('Unable to check analysis status. Please refresh the page.');
                }
            });
    }

    // Update step 2 to completed
    function updateStep2Completed() {
        const step2 = document.getElementById('step2');
        step2.innerHTML = `
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-green-500 text-white flex-shrink-0">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">AI skill gap analysis</p>
                <p class="text-xs text-gray-500">Analysis completed successfully</p>
            </div>
        `;
    }

    // Update step 3 to completed
    function updateStep3Completed() {
        const step3 = document.getElementById('step3');
        step3.innerHTML = `
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-green-500 text-white flex-shrink-0">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">Generating recommendations</p>
                <p class="text-xs text-gray-500">Mentor recommendations ready</p>
            </div>
        `;
    }

    // Update step 2 to processing
    function updateStep2Processing() {
        // Already showing as processing, no change needed
    }

    // Show error message
    function showError(message) {
        document.getElementById('statusMessage').innerHTML = `
            <span class="text-red-600">${message}</span>
        `;

        const step2 = document.getElementById('step2');
        step2.innerHTML = `
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-500 text-white flex-shrink-0">
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-red-600">Analysis failed</p>
                <p class="text-xs text-red-500">${message}</p>
            </div>
        `;
    }

    // Start polling immediately and then every 2 seconds
    pollStatus();
    pollInterval = setInterval(pollStatus, 2000);

    // Add smooth transition to status message
    document.getElementById('statusMessage').style.transition = 'opacity 0.3s';
</script>
@endsection
