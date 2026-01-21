<?php

namespace Naviisml\ApiGuard\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Naviisml\ApiGuard\Models\ApiKey;
use Naviisml\ApiGuard\Models\RateLimiter;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class ApiKeyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ApiKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_key' => Str::random(26),
            'private_key' => Str::random(26),
            'revoked_at' => null,
        ];
    }

    //    public function configure(): void
    //    {
    //        $this->afterCreating(function (ApiKey $apiKey) {
    //            RateLimiter::factory()->create([
    //                'api_key_id' => $apiKey->getKey()
    //            ]);
    //        });
    //    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => now(),
        ]);
    }
}
