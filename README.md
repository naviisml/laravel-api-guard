# Laravel API Guard

A lightweight Laravel package to authenticate requests using API keys via a custom guard.

## Features

- Auth guard: `auth:apikey`
- Secure access with public/private key headers
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

### Generate Keys

Run the artisan command:

```sh
php artisan make:api-keys
```
