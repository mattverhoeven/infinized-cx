<?php

namespace App\Models;

use App\Enums\CxChannel;
use App\Enums\CxInteractionState;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property int|null $cx_agent_id
 * @property CxChannel $channel
 * @property CxInteractionState $state
 * @property string|null $genesys_conversation_id
 * @property string|null $genesys_session_id
 * @property int|null $infinized_conversation_id
 * @property string|null $ani
 * @property string|null $dnis
 * @property array<string, mixed>|null $input_variables
 * @property array<string, mixed>|null $output_variables
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $started_at
 * @property Carbon|null $ended_at
 */
#[Fillable([
    'team_id',
    'cx_agent_id',
    'channel',
    'state',
    'genesys_conversation_id',
    'genesys_session_id',
    'infinized_conversation_id',
    'ani',
    'dnis',
    'input_variables',
    'output_variables',
    'metadata',
    'started_at',
    'ended_at',
])]
class CxInteraction extends Model
{
    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return BelongsTo<CxAgent, $this>
     */
    public function cxAgent(): BelongsTo
    {
        return $this->belongsTo(CxAgent::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => CxChannel::class,
            'state' => CxInteractionState::class,
            'input_variables' => 'array',
            'output_variables' => 'array',
            'metadata' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
