# Laravel API Guard

A lightweight Laravel package to authenticate requests using API keys via a custom guard.

## Features

- Auth guard: `auth:apikey`
- Secure access with public/private key headers on every request
- Resolve the key owner as the authenticated user (`$request->user()`)
- Reject revoked or ownerless keys
- Artisan command to generate keys

## Installation

1. Install the package (`composer require naviisml/laravel-api-guard`)
2. Publish the configuration* (`php artisan api-guard:install`)

_*This step is optional._

3. A auth guard will automatically be added if it doesn't exist in the `config/auth.php` configuration.

```shell
'guards' => [
    'apikey' => [
        'driver' => 'apikey',
        'provider' => 'users', // Optional
    ],
],
```

## Usage

### Authentication Guard

Use `auth:apikey` on API routes:

```php
Route::middleware('auth:apikey')->group(function () {
    Route::get('/secure-endpoint', fn() => 'Access granted');
});
```

### Required Headers

| Header          | Description     |
|-----------------|-----------------|
| `X-Public-Key`  | Public API key  |
| `X-Private-Key` | Private API key |

### Key ownership

Every API key must have a `user_id` pointing to a user resolvable by the guard's configured auth provider. Both `X-Public-Key` and `X-Private-Key` are required, including for GET and DELETE requests. An existing key without an owner cannot authenticate until its `user_id` is assigned.

The package registers the `apikey` guard if it is missing and preserves any existing `auth.guards.apikey` configuration.

### Generate Keys

Run the artisan command:

```sh
php artisan make:api-keys
```
