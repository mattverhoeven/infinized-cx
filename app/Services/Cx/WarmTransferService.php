<?php

namespace App\Services\Cx;

use App\Models\CxInteraction;
use App\Services\Genesys\GenesysApiClient;
use Illuminate\Support\Facades\Http;

/**
 * After-call and warm-transfer briefing for human agents.
 */
class WarmTransferService
{
    /**
     * @param  array<string, mixed>  $briefing
     */
    public function writeBriefing(CxInteraction $interaction, array $briefing): void
    {
        $team = $interaction->team;
        $genesys = $team->genesysConnection;

        if ($genesys === null || $interaction->genesys_conversation_id === null) {
            return;
        }

        try {
            $client = GenesysApiClient::fromConnection($genesys);
            $token = $client->accessToken();

            Http::withToken($token)
                ->patch(
                    'https://api.'.$genesys->region.'/api/v2/conversations/'.$interaction->genesys_conversation_id,
                    [
                        'attributes' => [
                            'cx_transfer_summary' => $briefing['summary'] ?? '',
                            'cx_transfer_queue' => $briefing['queue'] ?? '',
                        ],
                    ],
                );
        } catch (\Throwable) {
            // Non-fatal: Architect output variables remain the primary handoff.
        }
    }

    public function buildBriefingFromInteraction(CxInteraction $interaction): array
    {
        $output = $interaction->output_variables ?? [];

        return [
            'summary' => (string) ($output['summary'] ?? 'Customer spoke with CX agent.'),
            'queue' => (string) ($output['transfer_target'] ?? $output['queue'] ?? ''),
            'disposition' => (string) ($output['disposition'] ?? ''),
        ];
    }
}
