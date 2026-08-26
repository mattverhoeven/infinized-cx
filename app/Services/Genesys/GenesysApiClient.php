<?php

namespace App\Services\Genesys;

use App\Models\GenesysConnection;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GenesysApiClient
{
    public function __construct(
        protected GenesysConnection $connection,
    ) {}

    public static function fromConnection(GenesysConnection $connection): self
    {
        return new self($connection);
    }

    /**
     * @return array<string, mixed>
     */
    public function verify(): array
    {
        $token = $this->accessToken();

        $response = Http::withToken($token)
            ->get($this->apiBaseUrl().'/api/v2/users/me');

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listQueues(): array
    {
        $response = Http::withToken($this->accessToken())
            ->get($this->apiBaseUrl().'/api/v2/routing/queues', ['pageSize' => 100]);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return (array) ($response->json('entities') ?? []);
    }

    public function accessToken(): string
    {
        if (
            $this->connection->access_token !== null
            && $this->connection->token_expires_at !== null
            && $this->connection->token_expires_at->isFuture()
        ) {
            return $this->connection->access_token;
        }

        $response = Http::asForm()->post($this->loginBaseUrl().'/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $this->connection->client_id,
            'client_secret' => $this->connection->client_secret,
        ]);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        $token = (string) $response->json('access_token', '');
        $expiresIn = (int) $response->json('expires_in', 3600);

        if ($token === '') {
            throw new RuntimeException('Genesys OAuth did not return an access token.');
        }

        $this->connection->forceFill([
            'access_token' => $token,
            'token_expires_at' => now()->addSeconds(max(60, $expiresIn - 60)),
        ])->save();

        return $token;
    }

    protected function loginBaseUrl(): string
    {
        return 'https://login.'.$this->connection->region;
    }

    protected function apiBaseUrl(): string
    {
        return 'https://api.'.$this->connection->region;
    }
}
