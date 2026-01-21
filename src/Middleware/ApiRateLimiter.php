<?php

namespace Naviisml\ApiGuard\Middleware;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Naviisml\ApiGuard\Models\ApiKey;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Check whether an API key has exceeded its requests.
     *
     * @param  mixed  $request
     */
    public function handle(Request $request, \Closure $next, string $driver): mixed
    {
        $publicKey = $request->header('X-Public-Key');

        $apiKey = ApiKey::query()->firstWhere('public_key', $publicKey);

        // Check if the API is still active
        if (! $apiKey->ratelimiter || $apiKey->ratelimiter->apiKey->is_revoked) {
            $this->throwValidationException(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Using Laravel's RateLimiter to handle request limiting
        $rateLimiter = app(\Illuminate\Cache\RateLimiter::class);

        // Check if the rate limit exceeded
        if ($rateLimiter->tooManyAttempts($this->throttleKey($publicKey), $apiKey->ratelimiter->limit)) {
            $this->throwValidationException(['message' => 'Too many requests'], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Allow the request and increment the count
        $rateLimiter->hit(
            key: $this->throttleKey($publicKey),
            decaySeconds: now()->diffInSeconds($apiKey->ratelimiter->reset_at)
        );

        return $next($request);
    }

    private function throwValidationException(array $data, int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY): void
    {
        throw new HttpResponseException(new JsonResponse($data, $statusCode));
    }

    private function throttleKey(string $publicKey): string
    {
        return 'api-rate-limit:'.$publicKey;
    }
}
