<?php

namespace App\Http\Controllers\Interactions;

use App\Http\Controllers\Controller;
use App\Models\CxInteraction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InteractionController extends Controller
{
    public function index(Request $request): Response
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $interactions = $team->cxInteractions()
            ->with('cxAgent:id,name,connector_id')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (CxInteraction $interaction) => [
                'id' => $interaction->id,
                'channel' => $interaction->channel->value,
                'state' => $interaction->state->value,
                'agent_name' => $interaction->cxAgent?->name,
                'ani' => $interaction->ani,
                'dnis' => $interaction->dnis,
                'infinized_conversation_id' => $interaction->infinized_conversation_id,
                'started_at' => $interaction->started_at?->toIso8601String(),
                'ended_at' => $interaction->ended_at?->toIso8601String(),
            ]);

        return Inertia::render('interactions/index', [
            'interactions' => $interactions,
        ]);
    }

    public function show(Request $request, string $current_team, CxInteraction $interaction): Response
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null && $interaction->team_id === $team->id, 404);

        $connection = $team->infinizedConnection;

        return Inertia::render('interactions/show', [
            'interaction' => [
                'id' => $interaction->id,
                'channel' => $interaction->channel->value,
                'state' => $interaction->state->value,
                'genesys_conversation_id' => $interaction->genesys_conversation_id,
                'infinized_conversation_id' => $interaction->infinized_conversation_id,
                'input_variables' => $interaction->input_variables,
                'output_variables' => $interaction->output_variables,
                'started_at' => $interaction->started_at?->toIso8601String(),
                'ended_at' => $interaction->ended_at?->toIso8601String(),
            ],
            'infinized_deep_link' => $connection && $interaction->infinized_conversation_id
                ? rtrim(config('infinized.url'), '/').'/'.$connection->infinized_organization_slug.'/chat'
                : null,
        ]);
    }
}
