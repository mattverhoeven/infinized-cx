<?php

namespace App\Http\Controllers;

use App\Enums\CxChannel;
use App\Enums\OnboardingStep;
use App\Models\InfinizedConnection;
use App\Models\Team;
use App\Services\Infinized\InfinizedClient;
use App\Services\Infinized\InfinizedProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class OnboardingController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        if ($team->hasCompletedOnboarding()) {
            return redirect()->route('dashboard', ['current_team' => $team->slug]);
        }

        $connection = $team->infinizedConnection;
        $step = $this->resolveStep($team, $connection);

        return Inertia::render('onboarding/wizard', [
            'step' => $step->value,
            'workspace' => [
                'name' => $team->name,
                'slug' => $team->slug,
            ],
            'infinized' => [
                'url' => config('infinized.url'),
                'connected' => $connection !== null,
                'organization_name' => $connection?->infinized_organization_name,
                'organization_slug' => $connection?->infinized_organization_slug,
                'mode' => $connection?->mode?->value,
                'is_partner_child' => $connection?->is_partner_child,
            ],
            'can_auto_provision' => config('infinized.operator_token') !== null
                && config('infinized.operator_token') !== '',
            'channels' => collect(CxChannel::cases())->map(fn (CxChannel $c) => [
                'value' => $c->value,
                'label' => $c->label(),
            ]),
        ]);
    }

    public function workspace(Request $request): RedirectResponse
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team->update(['name' => $validated['name']]);
        $team->advanceOnboarding(OnboardingStep::Infinized);

        return redirect()->route('onboarding.show');
    }

    public function infinized(Request $request, InfinizedProvisioner $provisioner): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $user = $request->user();

        abort_unless($team !== null && $user !== null, 404);

        $validated = $request->validate([
            'path' => ['required', Rule::in(['provision', 'api_key'])],
            'api_key' => ['required_if:path,api_key', 'nullable', 'string', 'starts_with:ifz_'],
        ]);

        try {
            if ($validated['path'] === 'provision') {
                $provisioner->provisionForUser($team, $user);
            } else {
                $provisioner->connectWithApiKey($team, (string) $validated['api_key']);
            }
        } catch (RuntimeException $e) {
            return back()->withErrors(['infinized' => $e->getMessage()]);
        }

        $team->advanceOnboarding(OnboardingStep::Agent);

        return redirect()->route('onboarding.show')
            ->with('status', 'infinized-connected');
    }

    public function agent(Request $request, InfinizedClient $client): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($team !== null && $connection !== null, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'instructions' => ['required', 'string', 'max:20000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', Rule::in(array_column(CxChannel::cases(), 'value'))],
        ]);

        $infinized = $client->forConnection($connection);

        $agentResponse = $infinized->createAgent([
            'name' => $validated['name'],
            'instructions' => $validated['instructions'],
            'status' => 'active',
            'enabled_tools' => ['genesys_cloud'],
            'metadata' => ['cx' => ['channels' => $validated['channels']]],
        ]);

        $agentData = $agentResponse['data'] ?? $agentResponse;

        $team->cxAgents()->create([
            'infinized_agent_id' => (int) ($agentData['id'] ?? 0),
            'name' => $validated['name'],
            'channels' => $validated['channels'],
            'status' => 'active',
            'voice_settings' => ['language' => 'en', 'barge_in' => true],
            'escalation_settings' => ['genesys_should_escalate' => true],
        ]);

        $team->completeOnboarding();

        return redirect()->route('dashboard', ['current_team' => $team->slug])
            ->with('status', 'onboarding-complete');
    }

    public function skipAgent(Request $request): RedirectResponse
    {
        $team = $request->user()?->currentTeam;

        abort_unless($team !== null, 404);

        abort_unless($team->hasInfinizedBrain(), 422, 'Connect Infinized before finishing onboarding.');

        $team->completeOnboarding();

        return redirect()->route('dashboard', ['current_team' => $team->slug])
            ->with('status', 'onboarding-complete');
    }

    protected function resolveStep(Team $team, ?InfinizedConnection $connection): OnboardingStep
    {
        if ($connection === null) {
            return $team->onboarding_step === OnboardingStep::Welcome
                ? OnboardingStep::Welcome
                : OnboardingStep::Infinized;
        }

        if ($team->onboarding_step === OnboardingStep::Welcome) {
            return OnboardingStep::Infinized;
        }

        if ($team->onboarding_step === OnboardingStep::Infinized || $team->onboarding_step === OnboardingStep::Agent) {
            return OnboardingStep::Agent;
        }

        return $team->onboarding_step;
    }
}
