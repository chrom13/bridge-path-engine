<?php

namespace App\Http\Controllers;

use App\Services\TorreApiService;
use App\Services\CareerStrategistService;
use App\Jobs\AnalyzeOpportunityJob;
use App\Models\AnalysisResult;
use App\DTOs\AnalysisResultDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class OpportunityController extends Controller
{
    /**
     * Torre API Service
     */
    private TorreApiService $torreApi;

    /**
     * Career Strategist Service
     */
    private CareerStrategistService $careerStrategist;

    public function __construct(TorreApiService $torreApi, CareerStrategistService $careerStrategist)
    {
        $this->torreApi = $torreApi;
        $this->careerStrategist = $careerStrategist;
    }

    /**
     * Show the home page with search
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Search for opportunities using Torre API
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        // Search using Torre API
        $searchResults = $this->torreApi->searchOpportunities($query, $limit, $offset);

        // Extract opportunities from results
        $opportunities = [];
        $total = 0;
        $aggregators = [];

        if ($searchResults) {
            $opportunities = $searchResults['results'] ?? [];
            $total = $searchResults['total'] ?? 0;
            $aggregators = $searchResults['aggregators'] ?? [];
        }

        return view('results', [
            'opportunities' => $opportunities,
            'query' => $query,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'aggregators' => $aggregators,
        ]);
    }

    /**
     * Apply to an opportunity
     */
    public function apply($id)
    {
        $user = Session::get('user');

        return redirect()
            ->route('opportunities.search')
            ->with('success', "Application submitted successfully for opportunity #{$id}!");
    }

    /**
     * Analyze skill gap between user and opportunity
     */
    public function analyze($id)
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to analyze opportunities.');
        }

        // Get user genome data
        $userGenome = $user['genome_data'] ?? [];

        if (empty($userGenome)) {
            return redirect()
                ->route('opportunities.search')
                ->with('error', 'Unable to load your profile data. Please log in again.');
        }

        // Generate unique analysis ID
        $analysisId = Str::uuid()->toString();

        // Create analysis result record
        AnalysisResult::create([
            'analysis_id' => $analysisId,
            'opportunity_id' => $id,
            'user_username' => $user['username'],
            'status' => 'pending',
        ]);

        // Dispatch the analysis job to the queue
        AnalyzeOpportunityJob::dispatch($analysisId, $id, $userGenome, $user['username']);

        // Redirect to analysis loading page
        return redirect()->route('opportunities.analysis-loading', $analysisId);
    }

    /**
     * Show analysis loading page with polling
     */
    public function analysisLoading($analysisId)
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to view analysis.');
        }

        $analysisResult = AnalysisResult::where('analysis_id', $analysisId)->first();

        if (!$analysisResult) {
            return redirect()
                ->route('opportunities.search')
                ->with('error', 'Analysis not found.');
        }

        // If already completed, redirect to results
        if ($analysisResult->isCompleted()) {
            return $this->showAnalysisResult($analysisResult, $user);
        }

        // If failed, show error
        if ($analysisResult->isFailed()) {
            return redirect()
                ->route('opportunities.search')
                ->with('error', 'Analysis failed: ' . $analysisResult->error_message);
        }

        // Show loading view
        return view('analysis-loading', [
            'analysisId' => $analysisId,
            'user' => $user,
        ]);
    }

    /**
     * Check analysis status (AJAX endpoint)
     */
    public function checkAnalysisStatus($analysisId)
    {
        $analysisResult = AnalysisResult::where('analysis_id', $analysisId)->first();

        if (!$analysisResult) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Analysis not found',
            ], 404);
        }

        return response()->json([
            'status' => $analysisResult->status,
            'result' => $analysisResult->result,
            'error_message' => $analysisResult->error_message,
        ]);
    }

    /**
     * Show analysis result
     */
    private function showAnalysisResult(AnalysisResult $analysisResult, array $user)
    {
        $result = $analysisResult->result;
        $analysis = AnalysisResultDTO::fromArray($result['analysis']);
        $opportunity = $result['opportunity'];

        return view('analysis', [
            'analysis' => $analysis,
            'opportunity' => $opportunity,
            'user' => $user,
        ]);
    }
}
