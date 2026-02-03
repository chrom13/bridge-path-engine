<?php

namespace App\DTOs;

class RadarChartDataDTO
{
    public function __construct(
        public readonly array $labels,
        public readonly array $userScores,
        public readonly array $jobRequirements
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            labels: $data['labels'] ?? [],
            userScores: $data['user_scores'] ?? [],
            jobRequirements: $data['job_requirements'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'labels' => $this->labels,
            'user_scores' => $this->userScores,
            'job_requirements' => $this->jobRequirements,
        ];
    }
}
