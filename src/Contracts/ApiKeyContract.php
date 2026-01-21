<?php

namespace Naviisml\ApiGuard\Contracts;

use Illuminate\Database\Eloquent\Relations\HasOne;

interface ApiKeyContract
{
    /**
     * Regenerate the public key for the api.
     */
    public function regeneratePublicKey(): void;

    /**
     * Regenerate the private key for the api.
     */
    public function regeneratePrivateKey(): void;

    public function ratelimiter(): HasOne;
}
