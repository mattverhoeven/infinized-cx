<?php

namespace App\Services\Cx;

use App\Enums\CxChannel;
use App\Enums\CxInteractionState;
use App\Models\CxAgent;
use App\Models\CxInteraction;
use App\Models\Team;
use App\Services\Infinized\InfinizedClient;
use App\Services\Speech\SpeechPipeline;
use RuntimeException;

class InteractionRuntime
{
    public function __construct(
        protected InfinizedClient $infinizedClient,
        protected SpeechPipeline $speech,
    ) {}

    /**
     * @param  array<string, mixed>  $inputVariables
     */
    public function startInteraction(
        Team $team,
        CxAgent $agent,
        CxChannel $channel,
        array $inputVariables = [],
        ?string $genesysConversationId = null,
        ?string $genesysSessionId = null,
        ?string $ani = null,
        ?string $dnis = null,
    ): CxInteraction {
        $connection = $team->infinizedConnection;

        if ($connection === null) {
            throw new RuntimeException('Workspace is not connected to Infinized.');
        }

        $client = $this->infinizedClient->forConnection($connection);

        $conversationPayload = [
            'agent_id' => $agent->infinized_agent_id,
            'title' => 'CX '.ucfirst(str_replace('_', ' ', $channel->value)),
            'metadata' => [
                'source' => 'cx',
                'channel' => $channel->value,
                'genesys_conversation_id' => $genesysConversationId,
                'genesys_session_id' => $genesysSessionId,
                'input_variables' => $inputVariables,
            ],
        ];

        $conversationResponse = $client->createConversation($conversationPayload);
        $conversationData = $conversationResponse['data'] ?? $conversationResponse;
        $infinizedConversationId = (int) ($conversationData['id'] ?? 0);

        return CxInteraction::create([
            'team_id' => $team->id,
            'cx_agent_id' => $agent->id,
            'channel' => $channel,
            'state' => CxInteractionState::Active,
            'genesys_conversation_id' => $genesysConversationId,
            'genesys_session_id' => $genesysSessionId,
            'infinized_conversation_id' => $infinizedConversationId > 0 ? $infinizedConversationId : null,
            'ani' => $ani,
            'dnis' => $dnis,
            'input_variables' => $inputVariables,
            'started_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function handleDigitalTurn(CxInteraction $interaction, string $userMessage, array $context = []): string
    {
        $team = $interaction->team;
        $connection = $team->infinizedConnection;

        if ($connection === null || $interaction->infinized_conversation_id === null) {
            throw new RuntimeException('Interaction is not wired to Infinized.');
        }

        $client = $this->infinizedClient->forConnection($connection);

        $reply = '';
        $client->streamConversationMessage(
            $interaction->infinized_conversation_id,
            [
                'content' => $userMessage,
                'metadata' => [
                    'cx_context' => $context,
                    'channel' => $interaction->channel->value,
                ],
            ],
            function (array $event) use (&$reply): void {
                if ($event['event'] === 'delta' && isset($event['data']['content'])) {
                    $reply .= (string) $event['data']['content'];
                }
            },
        );

        return trim($reply);
    }

    /**
     * @param  array<string, mixed>  $outputVariables
     */
    public function completeInteraction(CxInteraction $interaction, CxInteractionState $state, array $outputVariables = []): CxInteraction
    {
        $interaction->update([
            'state' => $state,
            'output_variables' => $outputVariables,
            'ended_at' => now(),
        ]);

        return $interaction->fresh() ?? $interaction;
    }

    public function handleVoiceTurn(CxInteraction $interaction, string $audioPayload): string
    {
        $transcript = $this->speech->transcribe($audioPayload);
        $reply = $this->handleDigitalTurn($interaction, $transcript);

        return $this->speech->synthesize($reply, $interaction->cxAgent?->voice_settings ?? []);
    }
}
