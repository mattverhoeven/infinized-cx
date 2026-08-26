<?php

namespace App\Enums;

enum CxChannel: string
{
    case GenesysVoice = 'genesys_voice';
    case GenesysDigital = 'genesys_digital';

    public function label(): string
    {
        return match ($this) {
            self::GenesysVoice => 'Voice',
            self::GenesysDigital => 'Chat & messaging',
        };
    }
}
