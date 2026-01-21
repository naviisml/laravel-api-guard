<?php

namespace Naviisml\ApiGuard\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Naviisml\ApiGuard\Models\ApiKey;
use Naviisml\ApiGuard\Models\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiGuardTest extends TestCase
{
    private ?ApiKey $apiKey;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/api/protected-route', function () {
            return true;
        })->middleware(['api', 'auth:apikey', 'ratelimiter:api']);

        Route::post('/api/protected-route', function () {
            return true;
        })->middleware(['api', 'auth:apikey', 'ratelimiter:api']);

        // Create a test API key
        $this->apiKey = ApiKey::query()->firstWhere('public_key', 'public_key');

        if (is_null($this->apiKey)) {
            $this->apiKey = ApiKey::factory()->create([
                'public_key' => 'public_key',
                'private_key' => 'private_key',
            ]);

            RateLimiter::factory()->create([
                'api_key_id' => $this->apiKey->id,
            ]);
        }

        $this->headers = [
            'X-Public-Key' => $this->apiKey->public_key,
            'X-Private-Key' => $this->apiKey->private_key,
        ];
    }

    #[Test]
    public function it_allows_get_requests_with_only_public_key()
    {
        $this->apiKey->ratelimiter->update([
            'requests' => 0, // Almost at limit
            'limit' => 10,
            'reset_at' => now()->addMinutes(10),
        ]);

        $response = $this->withHeaders([
            'X-Public-Key' => $this->apiKey->public_key,
        ])->getJson('/api/protected-route');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_denies_get_requests_without_public_key()
    {
        $response = $this->getJson('/api/protected-route');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid API credentials.']);
    }

    #[Test]
    public function it_allows_post_requests_with_valid_public_and_private_keys()
    {
        $this->apiKey->ratelimiter->update([
            'requests' => 0, // Almost at limit
            'limit' => 10,
            'reset_at' => now()->addMinutes(10),
        ]);

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/protected-route', ['data' => 'test']);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_denies_post_requests_with_only_public_key()
    {
        $response = $this->withHeaders([
            'X-Public-Key' => $this->apiKey->public_key,
        ])->postJson('/api/protected-route', ['data' => 'test']);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Missing API credentials.']);
    }

    #[Test]
    public function it_denies_post_requests_without_api_keys()
    {
        $response = $this->postJson('/api/protected-route', ['data' => 'test']);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid API credentials.']);
    }

    #[Test]
    public function it_enforces_rate_limiting()
    {
        $this->apiKey->ratelimiter->update([
            'requests' => 9, // Almost at limit
            'limit' => 10,
            'reset_at' => now()->addMinutes(10),
        ]);

        // Exceed the limit
        for ($i = 0; $i < 3; $i++) {
            $response = $this->withHeaders([
                'X-Public-Key' => $this->apiKey->public_key,
            ])->getJson('/api/protected-route');
        }

        $response->assertStatus(429)
            ->assertJson(['message' => 'Too many requests']);
    }

    #[Test]
    public function it_resets_rate_limit_after_time_expires()
    {
        $this->apiKey->ratelimiter->update([
            'requests' => 10, // Limit reached
            'limit' => 10,
            'reset_at' => now(), // Expired
        ]);

        // First request after reset should pass
        $response = $this->withHeaders([
            'X-Public-Key' => $this->apiKey->public_key,
        ])->getJson('/api/protected-route');

        $response->assertStatus(200);
    }
}
