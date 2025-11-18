<?php

namespace App\Enums;

enum JobStatus: string
{
    case NOT_APPLIED = 'not_applied';
    case APPLIED = 'applied';
    case INTERVIEW = 'interview_scheduled';
    case OFFER = 'offer_received';
    case REJECTED = 'rejected';
    case ON_HOLD = 'on_hold';

    public function label(): string
    {
        return match ($this) {
            self::NOT_APPLIED => 'Not applied yet',
            self::APPLIED => 'Applied',
            self::INTERVIEW => 'Interview scheduled',
            self::OFFER => 'Offer received',
            self::REJECTED => 'Rejected',
            self::ON_HOLD => 'On hold',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
