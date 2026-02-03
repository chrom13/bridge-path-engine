<?php

namespace App\Jobs;

use App\Models\AnalysisResult;
use App\Services\CareerStrategistService;
use App\Services\TorreApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeOpportunityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $analysisId,
        public string $opportunityId,
        public array $userGenome,
        public string $username
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        TorreApiService $torreApi,
        CareerStrategistService $careerStrategist
    ): void {
        Log::info("Starting AI analysis for opportunity", [
            'analysis_id' => $this->analysisId,
            'opportunity_id' => $this->opportunityId,
            'username' => $this->username,
        ]);

        // Update status to processing
        AnalysisResult::where('analysis_id', $this->analysisId)->update([
            'status' => 'processing',
        ]);

        try {
            // Fetch opportunity details
            $opportunity = $torreApi->getOpportunity($this->opportunityId);

            if (!$opportunity) {
                throw new \Exception('Failed to fetch opportunity details from Torre API');
            }

            // Perform AI analysis
            $analysis = $careerStrategist->analyzeSkillGap($this->userGenome, $opportunity);

            if (!$analysis) {
                throw new \Exception('AI analysis failed to generate results');
            }

            // Store the completed analysis
            AnalysisResult::where('analysis_id', $this->analysisId)->update([
                'status' => 'completed',
                'result' => [
                    'analysis' => $analysis->toArray(),
                    'opportunity' => $opportunity,
                ],
            ]);

            Log::info("AI analysis completed successfully", [
                'analysis_id' => $this->analysisId,
            ]);

        } catch (\Exception $e) {
            Log::error("AI analysis failed", [
                'analysis_id' => $this->analysisId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark as failed
            AnalysisResult::where('analysis_id', $this->analysisId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AnalyzeOpportunityJob failed permanently", [
            'analysis_id' => $this->analysisId,
            'error' => $exception->getMessage(),
        ]);

        AnalysisResult::where('analysis_id', $this->analysisId)->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
