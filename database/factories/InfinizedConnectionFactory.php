<?php

namespace Database\Factories;

use App\Enums\InfinizedConnectionMode;
use App\Models\InfinizedConnection;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InfinizedConnection>
 */
class InfinizedConnectionFactory extends Factory
{
    protected $model = InfinizedConnection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'infinized_organization_id' => fake()->numberBetween(1, 9999),
            'infinized_organization_slug' => fake()->unique()->slug(2),
            'infinized_organization_name' => fake()->company(),
            'api_key' => 'ifz_'.fake()->regexify('[A-Za-z0-9]{48}'),
            'mode' => InfinizedConnectionMode::PastedKey,
            'is_partner_child' => false,
            'last_health_check_at' => now(),
            'last_health_payload' => ['ok' => true],
        ];
    }
}
