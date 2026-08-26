<?php

namespace App\Models;

use App\Enums\CxChannel;
use Database\Factories\CxAgentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $team_id
 * @property int $infinized_agent_id
 * @property string $connector_id
 * @property string $name
 * @property array<int, string>|null $channels
 * @property array<string, mixed>|null $voice_settings
 * @property array<string, mixed>|null $escalation_settings
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team $team
 * @property-read Collection<int, CxDeployment> $deployments
 * @property-read Collection<int, CxInteraction> $interactions
 */
#[Fillable([
    'team_id',
    'infinized_agent_id',
    'connector_id',
    'name',
    'channels',
    'voice_settings',
    'escalation_settings',
    'status',
])]
class CxAgent extends Model
{
    /** @use HasFactory<CxAgentFactory> */
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CxAgent $agent) {
            if (empty($agent->connector_id)) {
                $agent->connector_id = Str::lower(Str::random(12));
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
     * @return HasMany<CxDeployment, $this>
     */
    public function deployments(): HasMany
    {
        return $this->hasMany(CxDeployment::class);
    }

    /**
     * @return HasMany<CxInteraction, $this>
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(CxInteraction::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'voice_settings' => 'array',
            'escalation_settings' => 'array',
        ];
    }

    public function supportsChannel(CxChannel $channel): bool
    {
        $channels = $this->channels ?? [];

        return in_array($channel->value, $channels, true);
    }
}
