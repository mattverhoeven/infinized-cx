<?php

namespace Database\Factories;

use App\Models\CxAgent;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CxAgent>
 */
class CxAgentFactory extends Factory
{
    protected $model = CxAgent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'infinized_agent_id' => fake()->numberBetween(1, 9999),
            'connector_id' => Str::lower(Str::random(12)),
            'name' => fake()->words(2, true),
            'channels' => ['genesys_digital'],
            'voice_settings' => ['language' => 'en'],
            'escalation_settings' => ['genesys_should_escalate' => true],
            'status' => 'active',
        ];
    }
}
