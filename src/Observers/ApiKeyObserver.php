<?php

namespace Naviisml\ApiGuard\Observers;

use Naviisml\ApiGuard\Models\ApiKey;
use Naviisml\ApiGuard\Models\RateLimiter;

class ApiKeyObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(ApiKey $apiKey): void
    {
        RateLimiter::create([
            'api_key_id' => $apiKey->id,
            'requests' => 100,
        ]);
    }
}
