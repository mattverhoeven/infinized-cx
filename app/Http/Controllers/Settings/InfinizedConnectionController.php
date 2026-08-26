<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Infinized\InfinizedClient;
use App\Services\Infinized\InfinizedProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class InfinizedConnectionController extends Controller
{
    public function edit(Request $request): Response
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $connection = $team->infinizedConnection;

        return Inertia::render('settings/infinized', [
            'connection' => $connection ? [
                'organization_slug' => $connection->infinized_organization_slug,
                'organization_name' => $connection->infinized_organization_name,
                'mode' => $connection->mode->value,
                'is_partner_child' => $connection->is_partner_child,
                'last_health_check_at' => $connection->last_health_check_at?->toIso8601String(),
                'last_health_payload' => $connection->last_health_payload,
            ] : null,
            'infinized_url' => config('infinized.url'),
        ]);
    }

    public function store(Request $request, InfinizedProvisioner $provisioner): RedirectResponse
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $validated = $request->validate([
            'api_key' => ['required', 'string', 'starts_with:ifz_'],
        ]);

        $provisioner->connectWithApiKey($team, $validated['api_key']);

        return back()->with('status', 'infinized-connected');
    }

    public function provision(Request $request, InfinizedProvisioner $provisioner): RedirectResponse
    {
        $user = $request->user();
        $team = $user?->currentTeam;

        abort_unless($team !== null && $user !== null, 404);

        try {
            $provisioner->provisionForUser($team, $user);
        } catch (RuntimeException $e) {
            return back()->withErrors(['provision' => $e->getMessage()]);
        }

        return back()->with('status', 'infinized-provisioned');
    }

    public function health(Request $request, InfinizedClient $client): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($connection !== null, 404);

        $verification = $client->forConnection($connection)->verifyConnection();

        $connection->update([
            'last_health_check_at' => now(),
            'last_health_payload' => $verification,
        ]);

        if (! $verification['ok']) {
            return back()->withErrors(['health' => $verification['error'] ?? 'Health check failed.']);
        }

        return back()->with('status', 'infinized-health-ok');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        if ($connection !== null) {
            $connection->delete();
        }

        return back()->with('status', 'infinized-disconnected');
    }
}
