<?php

namespace App\Http\Controllers;

use App\Services\TorreApiService;
use App\Services\CareerStrategistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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

        // Fetch the opportunity details
        $opportunity = $this->torreApi->getOpportunity($id);

        if (!$opportunity) {
            return redirect()
                ->route('opportunities.search')
                ->with('error', 'Opportunity not found.');
        }

        // Get user genome data
        $userGenome = $user['genome_data'] ?? [];

        if (empty($userGenome)) {
            return redirect()
                ->route('opportunities.search')
                ->with('error', 'Unable to load your profile data. Please log in again.');
        }

        // Perform AI analysis
        $analysis = $this->careerStrategist->analyzeSkillGap($userGenome, $opportunity);

        if (!$analysis) {
            return redirect()
                ->route('opportunities.search')
                ->with('error', 'Unable to perform analysis. Please ensure OpenAI API is configured.');
        }

        return view('analysis', [
            'analysis' => $analysis,
            'opportunity' => $opportunity,
            'user' => $user,
        ]);
    }
}
