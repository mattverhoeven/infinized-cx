<?php

namespace App\Http\Controllers\Genesys;

use App\Enums\CxChannel;
use App\Enums\CxInteractionState;
use App\Http\Controllers\Controller;
use App\Models\CxAgent;
use App\Services\Cx\InteractionRuntime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotConnectorController extends Controller
{
    /**
     * Genesys Bot Connector webhook endpoint.
     *
     * @return array{intent: string, messages?: array<int, array{text: string}>}
     */
    public function handle(Request $request, string $connectorId, InteractionRuntime $runtime): JsonResponse
    {
        $agent = CxAgent::query()->where('connector_id', $connectorId)->first();

        if ($agent === null) {
            return response()->json(['intent' => 'failure'], 404);
        }

        $team = $agent->team;
        $payload = $request->all();
        $userMessage = (string) ($payload['text'] ?? $payload['message'] ?? $payload['input'] ?? '');
        $genesysConversationId = $payload['conversationId'] ?? $payload['conversation_id'] ?? null;

        $interaction = $runtime->startInteraction(
            team: $team,
            agent: $agent,
            channel: CxChannel::GenesysDigital,
            inputVariables: $payload['sessionVariables'] ?? $payload,
            genesysConversationId: is_string($genesysConversationId) ? $genesysConversationId : null,
        );

        try {
            $reply = $runtime->handleDigitalTurn($interaction, $userMessage, $payload);

            $shouldEscalate = str_contains(strtolower($reply), 'escalat')
                || ($payload['genesys_should_escalate'] ?? false);

            if ($shouldEscalate) {
                $runtime->completeInteraction($interaction, CxInteractionState::Escalated, [
                    'genesys_should_escalate' => true,
                    'summary' => $reply,
                ]);

                return response()->json(['intent' => 'escalate']);
            }

            $runtime->completeInteraction($interaction, CxInteractionState::Completed, [
                'summary' => $reply,
            ]);

            return response()->json([
                'intent' => 'success',
                'messages' => [['text' => $reply]],
            ]);
        } catch (\Throwable $e) {
            $runtime->completeInteraction($interaction, CxInteractionState::Failed, [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['intent' => 'failure']);
        }
    }
}
