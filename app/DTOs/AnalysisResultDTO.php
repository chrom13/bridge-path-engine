<?php

namespace App\DTOs;

class AnalysisResultDTO
{
    /**
     * @param array<SkillGapDTO> $skillGaps
     * @param array<MentorRecommendationDTO> $mentorRecommendations
     */
    public function __construct(
        public readonly string $analysisSummary,
        public readonly RadarChartDataDTO $radarChartData,
        public readonly array $skillGaps,
        public readonly array $mentorRecommendations,
        public readonly string $upsellMessage
    ) {
    }

    public static function fromArray(array $data): self
    {
        $skillGaps = array_map(
            fn($gap) => SkillGapDTO::fromArray($gap),
            $data['gap_analysis_roadmap']['skill_gaps'] ?? []
        );

        $mentorRecommendations = array_map(
            fn($mentor) => MentorRecommendationDTO::fromArray($mentor),
            $data['gap_analysis_roadmap']['mentor_recommendations'] ?? []
        );

        return new self(
            analysisSummary: $data['analysis_summary'] ?? '',
            radarChartData: RadarChartDataDTO::fromArray($data['radar_chart_data'] ?? []),
            skillGaps: $skillGaps,
            mentorRecommendations: $mentorRecommendations,
            upsellMessage: $data['gap_analysis_roadmap']['upsell_message'] ?? ''
        );
    }

    public function toArray(): array
    {
        return [
            'analysis_summary' => $this->analysisSummary,
            'radar_chart_data' => $this->radarChartData->toArray(),
            'gap_analysis_roadmap' => [
                'skill_gaps' => array_map(fn($gap) => $gap->toArray(), $this->skillGaps),
                'mentor_recommendations' => array_map(fn($mentor) => $mentor->toArray(), $this->mentorRecommendations),
                'upsell_message' => $this->upsellMessage,
            ],
        ];
    }
}
