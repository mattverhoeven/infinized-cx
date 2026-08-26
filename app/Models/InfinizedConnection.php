<?php

namespace App\Models;

use App\Enums\InfinizedConnectionMode;
use Database\Factories\InfinizedConnectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property int|null $infinized_organization_id
 * @property string $infinized_organization_slug
 * @property string|null $infinized_organization_name
 * @property string $api_key
 * @property InfinizedConnectionMode $mode
 * @property bool $is_partner_child
 * @property Carbon|null $last_health_check_at
 * @property array<string, mixed>|null $last_health_payload
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'team_id',
    'infinized_organization_id',
    'infinized_organization_slug',
    'infinized_organization_name',
    'api_key',
    'mode',
    'is_partner_child',
    'last_health_check_at',
    'last_health_payload',
])]
class InfinizedConnection extends Model
{
    /** @use HasFactory<InfinizedConnectionFactory> */
    use HasFactory;

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
            'api_key' => 'encrypted',
            'mode' => InfinizedConnectionMode::class,
            'is_partner_child' => 'boolean',
            'last_health_check_at' => 'datetime',
            'last_health_payload' => 'array',
        ];
    }
}
