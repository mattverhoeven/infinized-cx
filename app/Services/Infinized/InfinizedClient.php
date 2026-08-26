<?php

namespace App\Services\Infinized;

use App\Models\InfinizedConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class InfinizedClient
{
    public function __construct(
        protected ?string $baseUrl = null,
        protected ?string $apiKey = null,
    ) {}

    public function forConnection(InfinizedConnection $connection): self
    {
        return new self(
            baseUrl: config('infinized.url'),
            apiKey: $connection->api_key,
        );
    }

    public function asOperator(?string $organizationSlug = null): self
    {
        $token = config('infinized.operator_token');

        if ($token === null || $token === '') {
            throw new RuntimeException('INFINIZED_OPERATOR_TOKEN is not configured.');
        }

        $client = new self(
            baseUrl: config('infinized.url'),
            apiKey: $token,
        );

        if ($organizationSlug !== null) {
            $client->organizationHeader = $organizationSlug;
        }

        return $client;
    }

    protected ?string $organizationHeader = null;

    /**
     * @return array<string, mixed>
     */
    public function me(): array
    {
        return $this->get('me');
    }

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return $this->get('organization');
    }

    /**
     * @return array<string, mixed>
     */
    public function health(): array
    {
        return $this->get('health');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createAgent(array $payload): array
    {
        return $this->post('agents', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateAgent(int $agentId, array $payload): array
    {
        return $this->patch("agents/{$agentId}", $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function listAgents(): array
    {
        return $this->get('agents');
    }

    /**
     * @return array<string, mixed>
     */
    public function getAgent(int $agentId): array
    {
        return $this->get("agents/{$agentId}");
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createConversation(array $payload): array
    {
        return $this->post('conversations', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function streamMessage(int $conversationId, array $payload): array
    {
        return $this->post("conversations/{$conversationId}/messages/stream", $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function testChat(array $payload): array
    {
        return $this->post('test/chat', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function listKnowledge(): array
    {
        return $this->get('knowledge');
    }

    /**
     * @return array<string, mixed>
     */
    public function searchKnowledge(string $query): array
    {
        return $this->get('knowledge/search', ['q' => $query]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createKnowledgeSource(array $payload): array
    {
        return $this->post('knowledge', $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function provisionPartnerTenant(array $payload): array
    {
        return $this->post('partner/tenants', $payload);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        $response = $this->request()->get($this->url($path), $query);

        $this->throwIfFailed($response);

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function post(string $path, array $payload = []): array
    {
        $response = $this->request()->post($this->url($path), $payload);

        $this->throwIfFailed($response);

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function patch(string $path, array $payload = []): array
    {
        $response = $this->request()->patch($this->url($path), $payload);

        $this->throwIfFailed($response);

        return $response->json() ?? [];
    }

    /**
     * Stream SSE from Infinized conversation endpoint.
     *
     * @param  array<string, mixed>  $payload
     */
    public function streamConversationMessage(int $conversationId, array $payload, callable $onEvent): void
    {
        $url = $this->url("conversations/{$conversationId}/messages/stream");

        $response = $this->request()
            ->withOptions(['stream' => true])
            ->post($url, $payload);

        $this->throwIfFailed($response);

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $chunk = $body->read(8192);

            if ($chunk === '') {
                continue;
            }

            $buffer .= $chunk;

            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $eventBlock = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);
                $onEvent($this->parseSseBlock($eventBlock));
            }
        }
    }

    /**
     * @return array{event: string, data: array<string, mixed>|null}
     */
    protected function parseSseBlock(string $block): array
    {
        $event = 'message';
        $data = null;

        foreach (explode("\n", $block) as $line) {
            if (str_starts_with($line, 'event:')) {
                $event = trim(substr($line, 6));
            } elseif (str_starts_with($line, 'data:')) {
                $json = trim(substr($line, 5));
                $data = json_decode($json, true);
            }
        }

        return ['event' => $event, 'data' => is_array($data) ? $data : null];
    }

    protected function request(): PendingRequest
    {
        if ($this->apiKey === null || $this->apiKey === '') {
            throw new RuntimeException('Infinized API key is not configured.');
        }

        $request = Http::baseUrl($this->baseUrl ?? config('infinized.url'))
            ->timeout(config('infinized.timeout'))
            ->connectTimeout(config('infinized.connect_timeout'))
            ->acceptJson()
            ->withToken($this->apiKey);

        if ($this->organizationHeader !== null) {
            $request = $request->withHeaders([
                'X-Infinized-Organization' => $this->organizationHeader,
            ]);
        }

        return $request;
    }

    protected function url(string $path): string
    {
        return rtrim($this->baseUrl ?? config('infinized.url'), '/').'/api/v1/'.ltrim($path, '/');
    }

    /**
     * @param  Response  $response
     */
    protected function throwIfFailed($response): void
    {
        if ($response->failed()) {
            throw new RequestException($response);
        }
    }

    /**
     * @return array{ok: bool, organization?: array<string, mixed>, error?: string}
     */
    public function verifyConnection(): array
    {
        try {
            $me = $this->me();
            $organization = $this->organization();

            return [
                'ok' => true,
                'me' => $me,
                'organization' => $organization['data'] ?? $organization,
            ];
        } catch (RequestException $e) {
            return [
                'ok' => false,
                'error' => $e->response?->json('message') ?? $e->getMessage(),
            ];
        } catch (ConnectionException $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
