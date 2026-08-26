<?php

namespace App\Services\Infinized;

use App\Enums\InfinizedConnectionMode;
use App\Models\InfinizedConnection;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InfinizedProvisioner
{
    public function __construct(
        protected InfinizedClient $client,
    ) {}

    public function connectWithApiKey(Team $team, string $apiKey): InfinizedConnection
    {
        $client = new InfinizedClient(apiKey: $apiKey);
        $verification = $client->verifyConnection();

        if (! $verification['ok']) {
            throw new RuntimeException($verification['error'] ?? 'Could not verify Infinized API key.');
        }

        $org = $verification['organization'] ?? [];

        return $this->storeConnection(
            team: $team,
            apiKey: $apiKey,
            mode: InfinizedConnectionMode::PastedKey,
            organizationId: isset($org['id']) ? (int) $org['id'] : null,
            organizationSlug: (string) ($org['slug'] ?? ''),
            organizationName: isset($org['name']) ? (string) $org['name'] : null,
            isPartnerChild: false,
            healthPayload: $verification,
        );
    }

    public function provisionForUser(Team $team, User $user): InfinizedConnection
    {
        $operator = $this->client->asOperator();

        $payload = [
            'email' => $user->email,
            'name' => $team->name,
            'workspace_name' => $team->name,
            'owner_name' => $user->name,
            'partner_organization_slug' => config('infinized.partner_organization_slug'),
            'api_key_abilities' => config('infinized.default_api_key_abilities'),
        ];

        $response = $operator->provisionPartnerTenant($payload);
        $data = $response['data'] ?? $response;

        $organization = $data['organization'] ?? [];
        $apiKey = $data['api_key'] ?? null;

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Infinized did not return an API key for the provisioned tenant.');
        }

        return $this->storeConnection(
            team: $team,
            apiKey: $apiKey,
            mode: InfinizedConnectionMode::Provisioned,
            organizationId: isset($organization['id']) ? (int) $organization['id'] : null,
            organizationSlug: (string) ($organization['slug'] ?? ''),
            organizationName: isset($organization['name']) ? (string) $organization['name'] : null,
            isPartnerChild: true,
            healthPayload: ['provisioned' => true],
        );
    }

    /**
     * @param  array<string, mixed>  $healthPayload
     */
    protected function storeConnection(
        Team $team,
        string $apiKey,
        InfinizedConnectionMode $mode,
        ?int $organizationId,
        string $organizationSlug,
        ?string $organizationName,
        bool $isPartnerChild,
        array $healthPayload,
    ): InfinizedConnection {
        return DB::transaction(function () use (
            $team,
            $apiKey,
            $mode,
            $organizationId,
            $organizationSlug,
            $organizationName,
            $isPartnerChild,
            $healthPayload,
        ): InfinizedConnection {
            $connection = InfinizedConnection::query()->updateOrCreate(
                ['team_id' => $team->id],
                [
                    'infinized_organization_id' => $organizationId,
                    'infinized_organization_slug' => $organizationSlug,
                    'infinized_organization_name' => $organizationName,
                    'api_key' => $apiKey,
                    'mode' => $mode,
                    'is_partner_child' => $isPartnerChild,
                    'last_health_check_at' => now(),
                    'last_health_payload' => $healthPayload,
                ],
            );

            return $connection;
        });
    }
}
