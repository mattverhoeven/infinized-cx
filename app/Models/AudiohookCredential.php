<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $team_id
 * @property string $api_key
 * @property string $client_secret
 */
#[Fillable(['team_id', 'api_key', 'client_secret'])]
class AudiohookCredential extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (AudiohookCredential $credential) {
            if (empty($credential->api_key)) {
                $credential->api_key = 'cx_'.Str::random(32);
            }

            if (empty($credential->client_secret)) {
                $credential->client_secret = Str::random(48);
            }
        });
    }

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
            'client_secret' => 'encrypted',
        ];
    }
}
