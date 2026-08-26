<?php

namespace App\Http\Controllers\Agents;

use App\Enums\CxChannel;
use App\Http\Controllers\Controller;
use App\Models\CxAgent;
use App\Services\Infinized\InfinizedClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CxAgentController extends Controller
{
    public function index(Request $request): Response
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $agents = $team->cxAgents()->orderBy('name')->get()->map(fn (CxAgent $agent) => [
            'id' => $agent->id,
            'name' => $agent->name,
            'connector_id' => $agent->connector_id,
            'infinized_agent_id' => $agent->infinized_agent_id,
            'channels' => $agent->channels ?? [],
            'status' => $agent->status,
        ]);

        return Inertia::render('agents/index', [
            'agents' => $agents,
            'hasInfinizedConnection' => $team->infinizedConnection !== null,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('agents/create', [
            'channels' => collect(CxChannel::cases())->map(fn (CxChannel $c) => [
                'value' => $c->value,
                'label' => $c->label(),
            ]),
        ]);
    }

    public function store(Request $request, InfinizedClient $client): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($team !== null && $connection !== null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string', 'max:20000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', 'in:'.implode(',', array_column(CxChannel::cases(), 'value'))],
        ]);

        $infinized = $client->forConnection($connection);

        $agentResponse = $infinized->createAgent([
            'name' => $validated['name'],
            'instructions' => $validated['instructions'],
            'status' => 'active',
            'enabled_tools' => in_array(CxChannel::GenesysDigital->value, $validated['channels'], true)
                || in_array(CxChannel::GenesysVoice->value, $validated['channels'], true)
                ? ['genesys_cloud']
                : [],
            'metadata' => ['cx' => ['channels' => $validated['channels']]],
        ]);

        $agentData = $agentResponse['data'] ?? $agentResponse;
        $infinizedAgentId = (int) ($agentData['id'] ?? 0);

        $cxAgent = $team->cxAgents()->create([
            'infinized_agent_id' => $infinizedAgentId,
            'name' => $validated['name'],
            'channels' => $validated['channels'],
            'status' => 'active',
            'voice_settings' => [
                'language' => 'en',
                'barge_in' => true,
            ],
            'escalation_settings' => [
                'genesys_should_escalate' => true,
            ],
        ]);

        return redirect()->route('agents.show', [
            'current_team' => $team->slug,
            'agent' => $cxAgent->id,
        ]);
    }

    public function show(Request $request, string $current_team, CxAgent $agent): Response
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null && $agent->team_id === $team->id, 404);

        $connection = $team->infinizedConnection;
        $infinizedAgent = null;

        if ($connection !== null) {
            try {
                $response = $client = app(InfinizedClient::class)->forConnection($connection)->getAgent($agent->infinized_agent_id);
                $infinizedAgent = $response['data'] ?? $response;
            } catch (\Throwable) {
                $infinizedAgent = null;
            }
        }

        return Inertia::render('agents/show', [
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'connector_id' => $agent->connector_id,
                'channels' => $agent->channels ?? [],
                'status' => $agent->status,
                'infinized_agent_id' => $agent->infinized_agent_id,
                'voice_settings' => $agent->voice_settings,
                'escalation_settings' => $agent->escalation_settings,
            ],
            'infinized_agent' => $infinizedAgent,
            'infinized_url' => config('infinized.url'),
            'organization_slug' => $connection?->infinized_organization_slug,
            'test_chat_reply' => session('test_chat_reply'),
        ]);
    }

    public function testChat(Request $request, string $current_team, CxAgent $agent, InfinizedClient $client): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($team !== null && $connection !== null && $agent->team_id === $team->id, 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:10000'],
        ]);

        $response = $client->forConnection($connection)->testChat([
            'agent_id' => $agent->infinized_agent_id,
            'message' => $validated['message'],
            'timeout_seconds' => 60,
        ]);

        session()->flash('test_chat_reply', $response['reply'] ?? $response['data']['content'] ?? json_encode($response));

        return back();
    }
}
