<?php

namespace App\Services;

use App\DTOs\AnalysisResultDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CareerStrategistService
{
    private string $provider;
    private string $apiKey;
    private string $model;
    private int $timeout;

    private const MENTOR_DATABASE = [
        'm_laravel_pro' => [
            'name' => 'Marcus Laravel',
            'expertise' => 'Laravel Expert & Backend Architecture',
        ],
        'm_frontend_wiz' => [
            'name' => 'Sarah Frontend',
            'expertise' => 'Frontend Development & UI/UX',
        ],
        'm_devops_guru' => [
            'name' => 'David DevOps',
            'expertise' => 'DevOps, CI/CD & Cloud Infrastructure',
        ],
        'm_soft_skills' => [
            'name' => 'Emma Leadership',
            'expertise' => 'Soft Skills, Leadership & Communication',
        ],
    ];

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'gemini');

        if ($this->provider === 'gemini') {
            $this->apiKey = config('services.gemini.api_key', '');
            $this->model = config('services.gemini.model', 'gemini-pro');
            $this->timeout = config('services.gemini.timeout', 60);
        } else {
            $this->apiKey = config('services.openai.api_key', '');
            $this->model = config('services.openai.model', 'gpt-3.5-turbo');
            $this->timeout = config('services.openai.timeout', 60);
        }
    }

    /**
     * Analyze skill gaps between user genome and job opportunity
     *
     * @param array $userGenome Torre user genome data
     * @param array $opportunity Torre opportunity data
     * @return AnalysisResultDTO|null
     */
    public function analyzeSkillGap(array $userGenome, array $opportunity): ?AnalysisResultDTO
    {
        if (empty($this->apiKey)) {
            Log::error("AI API key not configured for provider: {$this->provider}");
            return null;
        }

        if ($this->provider === 'gemini') {
            return $this->analyzeWithGemini($userGenome, $opportunity);
        }

        return $this->analyzeWithOpenAI($userGenome, $opportunity);
    }

    /**
     * Analyze using OpenAI API
     */
    private function analyzeWithOpenAI(array $userGenome, array $opportunity): ?AnalysisResultDTO
    {
        try {
            $prompt = $this->buildPrompt($userGenome, $opportunity);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->getSystemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if (!$content) {
                Log::error('OpenAI API returned empty content');
                return null;
            }

            $analysisData = json_decode($content, true);

            if (!$analysisData) {
                Log::error('Failed to decode OpenAI response', ['content' => $content]);
                return null;
            }

            return $this->parseAnalysisResponse($analysisData);

        } catch (\Exception $e) {
            Log::error('OpenAI: Exception during analysis', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Analyze using Gemini API
     */
    private function analyzeWithGemini(array $userGenome, array $opportunity): ?AnalysisResultDTO
    {
        try {
            $prompt = $this->buildPrompt($userGenome, $opportunity);
            $systemPrompt = $this->getSystemPrompt();

            // Combine system and user prompts for Gemini
            $combinedPrompt = $systemPrompt . "\n\n" . $prompt;

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->apiKey}";

            $response = Http::timeout($this->timeout)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $combinedPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Gemini API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                Log::error('Gemini API returned empty content', ['response' => $data]);
                return null;
            }

            // Remove markdown code block formatting if present
            $content = preg_replace('/^```json\s*/s', '', $content);
            $content = preg_replace('/\s*```$/s', '', $content);
            $content = trim($content);

            $analysisData = json_decode($content, true);

            if (!$analysisData) {
                Log::error('Failed to decode Gemini response', ['content' => $content]);
                return null;
            }

            Log::info('Gemini analysis completed successfully');

            return $this->parseAnalysisResponse($analysisData);

        } catch (\Exception $e) {
            Log::error('Gemini: Exception during analysis', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Build the analysis prompt from user genome and opportunity
     */
    private function buildPrompt(array $userGenome, array $opportunity): string
    {
        // Extract user skills
        $userSkills = collect($userGenome['strengths'] ?? [])
            ->map(fn($skill) => [
                'name' => $skill['name'] ?? '',
                'proficiency' => $skill['proficiency'] ?? 'beginner',
                'weight' => $skill['weight'] ?? 0,
            ])
            ->toArray();

        // Extract job requirements
        $jobSkills = collect($opportunity['skills'] ?? [])
            ->map(fn($skill) => [
                'name' => $skill['name'] ?? '',
                'proficiency' => $skill['proficiency'] ?? 'proficient',
            ])
            ->toArray();

        $userSkillsJson = json_encode($userSkills, JSON_PRETTY_PRINT);
        $jobSkillsJson = json_encode($jobSkills, JSON_PRETTY_PRINT);
        $opportunityTitle = $opportunity['objective'] ?? 'Unknown Position';
        $userName = $userGenome['name'] ?? 'User';

        return <<<PROMPT
Analyze the skill gap between the candidate and job opportunity.

**Candidate Profile:**
Name: {$userName}
Current Skills:
{$userSkillsJson}

**Job Opportunity:**
Position: {$opportunityTitle}
Required Skills:
{$jobSkillsJson}

**Available Mentors:**
- m_laravel_pro: Marcus Laravel (Laravel Expert & Backend Architecture)
- m_frontend_wiz: Sarah Frontend (Frontend Development & UI/UX)
- m_devops_guru: David DevOps (DevOps, CI/CD & Cloud Infrastructure)
- m_soft_skills: Emma Leadership (Soft Skills, Leadership & Communication)

Provide your analysis in the exact JSON format specified in the system prompt.
PROMPT;
    }

    /**
     * Get the system prompt for the AI career strategist
     */
    private function getSystemPrompt(): string
    {
        return <<<SYSTEM
You are a 'Senior Career Strategist' specializing in the tech industry with expertise in skill gap analysis and career development.

**Task:**
Analyze the provided JSON data comparing a candidate's skills (Genome) against a job opportunity's requirements. Identify the top 3-5 skill gaps and generate a comprehensive roadmap.

**Output Requirements:**
You must respond with a valid JSON object containing these exact fields:

1. "analysis_summary": A 2-3 sentence overview of the candidate's match with the opportunity.

2. "radar_chart_data": An object for Chart.js radar visualization:
   {
     "labels": ["Skill 1", "Skill 2", "Skill 3", ...],  // 5-8 key skills
     "user_scores": [80, 60, 40, ...],  // User proficiency (0-100)
     "job_requirements": [90, 80, 70, ...]  // Job requirement level (0-100)
   }

3. "gap_analysis_roadmap": An object containing:
   {
     "skill_gaps": [
       {
         "skill": "Skill Name",
         "severity": "Critical|Moderate|Minor",
         "reason": "Why this gap matters for the role",
         "priority": 1  // 1-5, where 1 is highest priority
       }
     ],
     "mentor_recommendations": [
       {
         "mentor_id": "m_laravel_pro",  // Must be from the provided mentor list
         "mentor_name": "Marcus Laravel",
         "expertise": "Laravel Expert & Backend Architecture",
         "why": "Why this mentor is recommended",
         "focus_areas": ["Area 1", "Area 2"]
       }
     ],
     "upsell_message": "Upgrade to our $29/mo Premium Plan for unlimited mentorship sessions and personalized learning paths!"
   }

**Guidelines:**
- Prioritize MENTORSHIP over courses
- Recommend 1-3 mentors from the provided list
- Identify 3-5 skill gaps maximum
- Severity levels: Critical (must-have for role), Moderate (important), Minor (nice-to-have)
- Include the upsell message exactly as shown
- Map proficiency levels: expert=90-100, proficient=70-89, competent=50-69, beginner=0-49
- Ensure all JSON is valid and properly formatted
SYSTEM;
    }

    /**
     * Parse and validate the AI response into DTO
     */
    private function parseAnalysisResponse(array $data): ?AnalysisResultDTO
    {
        // Validate required fields
        if (!isset($data['analysis_summary']) || !isset($data['radar_chart_data']) || !isset($data['gap_analysis_roadmap'])) {
            Log::error('OpenAI response missing required fields', ['data' => $data]);
            return null;
        }

        try {
            return AnalysisResultDTO::fromArray($data);
        } catch (\Exception $e) {
            Log::error('Failed to parse analysis response into DTO', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            return null;
        }
    }

    /**
     * Get mentor information by ID
     */
    public static function getMentorInfo(string $mentorId): ?array
    {
        return self::MENTOR_DATABASE[$mentorId] ?? null;
    }

    /**
     * Get all available mentors
     */
    public static function getAllMentors(): array
    {
        return self::MENTOR_DATABASE;
    }
}
