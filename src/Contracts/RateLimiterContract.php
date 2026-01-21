<?php

namespace Naviisml\ApiGuard\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface RateLimiterContract
{
    /**
     * Regenerate the public key for the api.
     */
    public function reset(): void;

    public function apiKey(): BelongsTo;
}
