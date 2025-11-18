<?php

namespace App\Enums;

enum ConversationType: string
{
    case INTERVIEW = 'interview_prep';
    case NEGOTIATION = 'negotiation';
    case FOLLOWUP = 'followup';

    public function label(): string
    {
        return match ($this) {
            self::INTERVIEW => 'Interview preparation',
            self::NEGOTIATION => 'Offer negotiation',
            self::FOLLOWUP => 'Follow-up / Reapply',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
