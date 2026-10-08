<?php

namespace Naviisml\ApiGuard\Guards;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Naviisml\ApiGuard\Models\ApiKey;

class ApiGuard implements Guard
{
    protected ?Authenticatable $user = null;

    protected bool $resolved = false;

    protected Request $request;

    public function __construct(protected UserProvider $provider, ?Request $request = null)
    {
        $this->request = $request ?? request();
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        // Guard instances may be reused across requests (for example with Octane).
        if ($this->request !== request()) {
            $this->setRequest(request());
        }

        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        $publicKey = $this->request->header('X-Public-Key');
        $privateKey = $this->request->header('X-Private-Key');

        // A public key identifies the credential; the private key authenticates it.
        // Both are required on every HTTP method, including GET and DELETE.
        if (! is_string($publicKey) || $publicKey === ''
            || ! is_string($privateKey) || $privateKey === '') {
            return null;
        }

        $apiKey = ApiKey::query()->where('public_key', $publicKey)->first();

        if (! $apiKey
            || $apiKey->is_revoked
            || ! $apiKey->user_id
            || ! is_string($apiKey->private_key)
            || ! hash_equals($apiKey->private_key, $privateKey)) {
            return null;
        }

        // Resolve via the configured authentication provider, not a hard-coded User model.
        $owner = $this->provider->retrieveById($apiKey->user_id);

        if ($owner === null) {
            return null;
        }

        $this->setUser($owner);

        return $this->user;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return $this->check();
    }

    public function hasUser(): bool
    {
        return $this->user() !== null;
    }

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
        $this->resolved = true;
    }

    public function setRequest(Request $request): static
    {
        $this->request = $request;
        $this->user = null;
        $this->resolved = false;

        return $this;
    }
}
