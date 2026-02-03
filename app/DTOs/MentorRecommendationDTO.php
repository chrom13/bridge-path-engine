<?php

namespace App\DTOs;

class MentorRecommendationDTO
{
    public function __construct(
        public readonly string $mentorId,
        public readonly string $mentorName,
        public readonly string $expertise,
        public readonly string $why,
        public readonly array $focusAreas,
        public readonly int $recommendedHours
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            mentorId: $data['mentor_id'] ?? '',
            mentorName: $data['mentor_name'] ?? '',
            expertise: $data['expertise'] ?? '',
            why: $data['why'] ?? '',
            focusAreas: $data['focus_areas'] ?? [],
            recommendedHours: $data['recommended_hours'] ?? 10
        );
    }

    public function toArray(): array
    {
        return [
            'mentor_id' => $this->mentorId,
            'mentor_name' => $this->mentorName,
            'expertise' => $this->expertise,
            'why' => $this->why,
            'focus_areas' => $this->focusAreas,
            'recommended_hours' => $this->recommendedHours,
        ];
    }
}
