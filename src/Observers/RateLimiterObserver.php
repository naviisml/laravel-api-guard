<?php

namespace Naviisml\ApiGuard\Observers;

use Naviisml\ApiGuard\Models\RateLimiter;

class RateLimiterObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(RateLimiter $ratelimiter): void
    {
        $ratelimiter->reset_at = now()->addHour();
    }
}
