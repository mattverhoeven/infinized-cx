<?php

namespace App\Enums;

enum CxInteractionState: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Escalated = 'escalated';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Escalated => 'Escalated',
            self::Failed => 'Failed',
        };
    }
}
