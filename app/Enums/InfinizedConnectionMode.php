<?php

namespace App\Enums;

enum InfinizedConnectionMode: string
{
    case PastedKey = 'pasted_key';
    case OAuth = 'oauth';
    case Provisioned = 'provisioned';

    public function label(): string
    {
        return match ($this) {
            self::PastedKey => 'API key',
            self::OAuth => 'OAuth',
            self::Provisioned => 'Auto-provisioned',
        };
    }
}
