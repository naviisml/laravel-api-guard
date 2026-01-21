<?php

namespace Naviisml\ApiGuard\Guards;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Naviisml\ApiGuard\Models\ApiKey;

class ApiGuard implements Guard
{
    protected $user;

    protected $provider;

    protected $request;

    public function __construct(UserProvider $provider)
    {
        $this->request = app(Request::class);
        $this->provider = $provider;
    }

    /**
     * Determine if the current user is authenticated.
     */
    public function check(): bool
    {
        $publicKey = request()->header('X-Public-Key');
        $privateKey = request()->header('X-Private-Key');

        return $this->validate([
            'public_key' => $publicKey,
            'private_key' => $privateKey,
        ]);
    }

    /**
     * Determine if the current user is a guest.
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?Authenticatable
    {
        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     */
    public function id(): int|string|null
    {
        if ($this->check()) {
            return $this->user()->getAuthIdentifier();
        }

        return null;
    }

    protected function validatePostRequest(ApiKey $apiKey, ?string $privateApiKey = null): void
    {
        if (empty($privateApiKey)) {
            throw new AuthenticationException('Missing API credentials.');
        }

        if ($privateApiKey !== $apiKey->private_key) {
            throw new AuthenticationException('Invalid API credentials.');
        }
    }

    protected function validateApiKeys(?string $publicApiKey = null, ?string $privateApiKey = null): void
    {
        $apiKey = ApiKey::firstWhere('public_key', $publicApiKey);

        if (is_null($apiKey)) {
            throw new AuthenticationException('Invalid API credentials.');
        }

        if (! in_array($this->request->getMethod(), ['GET', 'DELETE'], true)) {
            $this->validatePostRequest($apiKey, $privateApiKey);
        }

        // set the user that is linked to the api key,
        // if the user doesnt exist.. yeah I dont know what then..
    }

    /**
     * Validate a user's credentials.
     */
    public function validate(array $credentials = []): bool
    {
        // GET requests: Validate public api key
        // Any other request: Validate public & private api key
        $this->validateApiKeys(
            publicApiKey: $this->request->header('X-Public-Key'),
            privateApiKey: $this->request->header('X-Private-Key')
        );

        /*$user = $this->provider->retrieveByCredentials($credentials);

        if ($user && hash_equals($user->private_key, $credentials['private_key'])) {
            $this->setUser($user);

            return true;
        }*/

        // set the user to the user linked to the api key

        return true;
    }

    /**
     * Determine if the guard has a user instance.
     */
    public function hasUser(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * Set the current user.
     */
    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }
}
