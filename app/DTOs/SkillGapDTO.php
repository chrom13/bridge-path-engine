<?php

namespace App\DTOs;

class SkillGapDTO
{
    public function __construct(
        public readonly string $skill,
        public readonly string $severity,
        public readonly string $reason,
        public readonly int $priority
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            skill: $data['skill'] ?? '',
            severity: $data['severity'] ?? 'Moderate',
            reason: $data['reason'] ?? '',
            priority: $data['priority'] ?? 0
        );
    }

    public function toArray(): array
    {
        return [
            'skill' => $this->skill,
            'severity' => $this->severity,
            'reason' => $this->reason,
            'priority' => $this->priority,
        ];
    }
}
