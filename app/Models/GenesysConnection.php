<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property string $region
 * @property string $client_id
 * @property string $client_secret
 * @property string|null $genesys_organization_id
 * @property string|null $access_token
 * @property Carbon|null $token_expires_at
 * @property Carbon|null $verified_at
 */
#[Fillable([
    'team_id',
    'region',
    'client_id',
    'client_secret',
    'genesys_organization_id',
    'access_token',
    'token_expires_at',
    'verified_at',
])]
class GenesysConnection extends Model
{
    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'client_id' => 'encrypted',
            'client_secret' => 'encrypted',
            'access_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
