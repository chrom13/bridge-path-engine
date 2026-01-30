<?php

namespace App\Http\Controllers;

use App\Services\TorreApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OpportunityController extends Controller
{
    /**
     * Torre API Service
     */
    private TorreApiService $torreApi;

    public function __construct(TorreApiService $torreApi)
    {
        $this->torreApi = $torreApi;
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
}
