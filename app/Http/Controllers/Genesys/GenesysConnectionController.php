<?php

namespace App\Http\Controllers\Genesys;

use App\Http\Controllers\Controller;
use App\Models\AudiohookCredential;
use App\Services\Genesys\GenesysApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GenesysConnectionController extends Controller
{
    public function edit(Request $request): Response
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $connection = $team->genesysConnection;
        $audiohook = $team->audiohookCredential ?? $team->audiohookCredential()->first();

        if ($audiohook === null && $team->infinizedConnection !== null) {
            $audiohook = AudiohookCredential::query()->create(['team_id' => $team->id]);
        }

        return Inertia::render('genesys/settings', [
            'connection' => $connection ? [
                'region' => $connection->region,
                'verified_at' => $connection->verified_at?->toIso8601String(),
                'genesys_organization_id' => $connection->genesys_organization_id,
            ] : null,
            'regions' => config('genesys.regions'),
            'audiohook' => $audiohook ? [
                'api_key' => $audiohook->api_key,
                'wss_uri' => rtrim(config('app.url'), '/').config('genesys.audiohook.path'),
            ] : null,
            'architect_recipe' => [
                'audio_connector_id' => '{connector_id}',
                'bot_intents' => config('genesys.bot_connector.intents'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $validated = $request->validate([
            'region' => ['required', 'string', 'in:'.implode(',', array_keys(config('genesys.regions')))],
            'client_id' => ['required', 'string', 'max:500'],
            'client_secret' => ['required', 'string', 'max:500'],
        ]);

        $connection = $team->genesysConnection()->updateOrCreate(
            ['team_id' => $team->id],
            [
                'region' => $validated['region'],
                'client_id' => $validated['client_id'],
                'client_secret' => $validated['client_secret'],
            ],
        );

        try {
            $me = GenesysApiClient::fromConnection($connection)->verify();
            $connection->update([
                'verified_at' => now(),
                'genesys_organization_id' => $me['organizationId'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors(['genesys' => $e->getMessage()]);
        }

        return back()->with('status', 'genesys-connected');
    }
}
