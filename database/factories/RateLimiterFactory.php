<?php

namespace Naviisml\ApiGuard\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Naviisml\ApiGuard\Models\ApiKey;
use Naviisml\ApiGuard\Models\RateLimiter;

class RateLimiterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RateLimiter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api_key_id' => ApiKey::factory()->create()->id,
            'requests' => 0,
            'limit' => 10,
            'reset_at' => now()->addMinutes(1),
        ];
    }
}
