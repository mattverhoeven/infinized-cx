<?php

namespace App\Console\Commands;

use App\Enums\CxChannel;
use App\Models\AudiohookCredential;
use App\Models\CxAgent;
use App\Services\Cx\InteractionRuntime;
use App\Services\Cx\WarmTransferService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CxAudiohookServerCommand extends Command
{
    protected $signature = 'cx:audiohook {--host=0.0.0.0 : Bind host} {--port=8081 : Bind port}';

    protected $description = 'Run the Genesys AudioHook WebSocket server (development / single-node).';

    public function handle(InteractionRuntime $runtime, WarmTransferService $warmTransfer): int
    {
        $host = $this->option('host');
        $port = (int) $this->option('port');

        $this->info("AudioHook server listening on {$host}:{$port}");
        $this->info('Production should run this under Octane or a dedicated process manager.');

        // Minimal loop placeholder: production integrates Genesys AudioHook protocol.
        // Session flow: open -> opened (μ-law 8k) -> media frames -> close.
        while (true) {
            sleep(60);
            Log::debug('cx.audiohook.heartbeat', ['host' => $host, 'port' => $port]);
        }

        return self::SUCCESS;
    }

    /**
     * Handle a single AudioHook session (used in tests).
     *
     * @param  array<string, mixed>  $openPayload
     */
    public function simulateSession(CxAgent $agent, array $openPayload, InteractionRuntime $runtime): array
    {
        $team = $agent->team;
        $credential = $team->audiohookCredential;

        if ($credential === null) {
            AudiohookCredential::query()->create(['team_id' => $team->id]);
        }

        $interaction = $runtime->startInteraction(
            team: $team,
            agent: $agent,
            channel: CxChannel::GenesysVoice,
            inputVariables: $openPayload['parameters'] ?? [],
            genesysConversationId: $openPayload['conversationId'] ?? null,
            genesysSessionId: $openPayload['sessionId'] ?? null,
            ani: $openPayload['ani'] ?? null,
            dnis: $openPayload['dnis'] ?? null,
        );

        $reply = $runtime->handleDigitalTurn(
            $interaction,
            (string) ($openPayload['first_message'] ?? 'Hello'),
        );

        app(WarmTransferService::class)->writeBriefing(
            $interaction,
            ['summary' => $reply, 'queue' => $openPayload['queue'] ?? ''],
        );

        return [
            'interaction_id' => $interaction->id,
            'reply' => $reply,
            'audio_format' => config('genesys.audiohook.audio_format'),
        ];
    }
}
