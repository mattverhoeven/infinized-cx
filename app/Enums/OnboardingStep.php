<?php

namespace App\Enums;

enum OnboardingStep: string
{
    case Welcome = 'welcome';
    case Infinized = 'infinized';
    case Agent = 'agent';
    case Complete = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::Welcome => 'Welcome',
            self::Infinized => 'Infinized',
            self::Agent => 'First agent',
            self::Complete => 'Ready',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::Welcome => 1,
            self::Infinized => 2,
            self::Agent => 3,
            self::Complete => 4,
        };
    }
}
