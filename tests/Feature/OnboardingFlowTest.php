<?php

use App\Enums\InfinizedConnectionMode;
use App\Enums\OnboardingStep;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

it('redirects incomplete workspaces to onboarding', function () {
    $user = User::factory()->needsOnboarding()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding.show'));
});

it('shows the onboarding wizard for new workspaces', function () {
    $user = User::factory()->needsOnboarding()->create();

    actingAs($user)
        ->get(route('onboarding.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/wizard')
            ->where('step', 'welcome')
            ->where('can_auto_provision', false),
        );
});

it('advances workspace step and connects infinized via api key', function () {
    Http::fake([
        '*/api/v1/me' => Http::response(['data' => ['type' => 'api_key']]),
        '*/api/v1/organization' => Http::response([
            'data' => [
                'id' => 42,
                'slug' => 'acme-cx',
                'name' => 'Acme CX',
            ],
        ]),
    ]);

    $user = User::factory()->needsOnboarding()->create();
    $team = $user->currentTeam;

    actingAs($user)
        ->post(route('onboarding.workspace'), ['name' => 'Acme Support'])
        ->assertRedirect(route('onboarding.show'));

    $team->refresh();
    expect($team->name)->toBe('Acme Support')
        ->and($team->onboarding_step)->toBe(OnboardingStep::Infinized);

    actingAs($user)
        ->post(route('onboarding.infinized'), [
            'path' => 'api_key',
            'api_key' => 'ifz_test_key_'.str_repeat('a', 40),
        ])
        ->assertRedirect(route('onboarding.show'));

    assertDatabaseHas('infinized_connections', [
        'team_id' => $team->id,
        'infinized_organization_slug' => 'acme-cx',
        'mode' => InfinizedConnectionMode::PastedKey->value,
    ]);

    $team->refresh();
    expect($team->onboarding_step)->toBe(OnboardingStep::Agent);
});

it('provisions infinized during onboarding', function () {
    Http::fake([
        '*/api/v1/partner/tenants' => Http::response([
            'data' => [
                'organization' => [
                    'id' => 99,
                    'slug' => 'provisioned-cx',
                    'name' => 'Provisioned',
                ],
                'api_key' => 'ifz_provisioned_'.str_repeat('b', 40),
            ],
        ], 201),
    ]);

    config(['infinized.operator_token' => 'ifo_operator_test']);

    $user = User::factory()->needsOnboarding()->create();
    $team = $user->currentTeam;

    actingAs($user)
        ->post(route('onboarding.infinized'), ['path' => 'provision'])
        ->assertRedirect(route('onboarding.show'));

    assertDatabaseHas('infinized_connections', [
        'team_id' => $team->id,
        'mode' => InfinizedConnectionMode::Provisioned->value,
        'is_partner_child' => true,
    ]);
});

it('completes onboarding after creating first agent', function () {
    Http::fake([
        '*/api/v1/agents' => Http::response([
            'data' => ['id' => 7, 'name' => 'Support'],
        ], 201),
    ]);

    $user = User::factory()->needsOnboarding()->create();
    $team = $user->currentTeam;

    Http::fake([
        '*/api/v1/me' => Http::response(['data' => ['type' => 'api_key']]),
        '*/api/v1/organization' => Http::response([
            'data' => ['id' => 1, 'slug' => 'cx', 'name' => 'CX'],
        ]),
        '*/api/v1/agents' => Http::response([
            'data' => ['id' => 7, 'name' => 'Support'],
        ], 201),
    ]);

    actingAs($user)
        ->post(route('onboarding.infinized'), [
            'path' => 'api_key',
            'api_key' => 'ifz_test_key_'.str_repeat('c', 40),
        ]);

    actingAs($user)
        ->post(route('onboarding.agent'), [
            'name' => 'Support',
            'instructions' => 'Help customers.',
            'channels' => ['genesys_digital'],
        ])
        ->assertRedirect(route('dashboard', ['current_team' => $team->slug]));

    $team->refresh();
    expect($team->hasCompletedOnboarding())->toBeTrue();

    assertDatabaseHas('cx_agents', [
        'team_id' => $team->id,
        'name' => 'Support',
        'infinized_agent_id' => 7,
    ]);
});

it('can skip agent step after infinized is connected', function () {
    Http::fake([
        '*/api/v1/me' => Http::response(['data' => ['type' => 'api_key']]),
        '*/api/v1/organization' => Http::response([
            'data' => ['id' => 1, 'slug' => 'cx', 'name' => 'CX'],
        ]),
    ]);

    $user = User::factory()->needsOnboarding()->create();
    $team = $user->currentTeam;

    actingAs($user)
        ->post(route('onboarding.infinized'), [
            'path' => 'api_key',
            'api_key' => 'ifz_test_key_'.str_repeat('d', 40),
        ]);

    actingAs($user)
        ->post(route('onboarding.skip-agent'))
        ->assertRedirect(route('dashboard', ['current_team' => $team->slug]));

    expect($team->fresh()->hasCompletedOnboarding())->toBeTrue();
});
