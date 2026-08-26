<?php

use App\Enums\InfinizedConnectionMode;
use App\Models\CxAgent;
use App\Models\InfinizedConnection;
use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

it('connects a workspace with a pasted infinized api key', function () {
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

    $user = User::factory()->create();
    $team = $user->currentTeam;

    actingAs($user)
        ->post('/settings/infinized', [
            'api_key' => 'ifz_test_key_'.str_repeat('a', 40),
        ])
        ->assertRedirect();

    assertDatabaseHas('infinized_connections', [
        'team_id' => $team->id,
        'infinized_organization_slug' => 'acme-cx',
        'mode' => InfinizedConnectionMode::PastedKey->value,
    ]);
});

it('provisions an infinized org via operator api', function () {
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

    $user = User::factory()->create();
    $team = $user->currentTeam;

    actingAs($user)
        ->post('/settings/infinized/provision')
        ->assertRedirect();

    assertDatabaseHas('infinized_connections', [
        'team_id' => $team->id,
        'infinized_organization_slug' => 'provisioned-cx',
        'mode' => InfinizedConnectionMode::Provisioned->value,
        'is_partner_child' => true,
    ]);
});

it('handles genesys bot connector turns', function () {
    $connection = InfinizedConnection::factory()->create();
    $team = $connection->team;
    $agent = CxAgent::factory()->create([
        'team_id' => $team->id,
        'connector_id' => 'testconnector',
    ]);

    Http::fake([
        '*/api/v1/conversations' => Http::response([
            'data' => ['id' => 500, 'title' => 'CX test'],
        ], 201),
        '*/api/v1/conversations/500/messages/stream' => Http::response(
            "event: delta\ndata: {\"content\":\"Hello from CX\"}\n\n".
            "event: done\ndata: {}\n\n",
            200,
            ['Content-Type' => 'text/event-stream'],
        ),
    ]);

    $response = post('/bot/genesys/'.$agent->connector_id, [
        'text' => 'Hi there',
        'conversationId' => 'gc-123',
    ]);

    $response->assertOk()->assertJson([
        'intent' => 'success',
    ]);
});
