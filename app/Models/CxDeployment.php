<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cx_agent_id
 * @property string $type
 * @property string|null $genesys_integration_id
 * @property string|null $genesys_integration_name
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $last_probe_at
 * @property string|null $last_probe_status
 */
#[Fillable([
    'cx_agent_id',
    'type',
    'genesys_integration_id',
    'genesys_integration_name',
    'metadata',
    'last_probe_at',
    'last_probe_status',
])]
class CxDeployment extends Model
{
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
            'metadata' => 'array',
            'last_probe_at' => 'datetime',
        ];
    }
}
