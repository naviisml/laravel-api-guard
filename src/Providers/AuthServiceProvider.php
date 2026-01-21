<?php

namespace Naviisml\ApiGuard\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Naviisml\ApiGuard\Guards\ApiGuard;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        $this->registerGuards();

        Auth::extend('apikey', function ($app, $name, array $config) {
            return new ApiGuard(Auth::createUserProvider($config['provider']));
        });
    }

    private function registerGuards()
    {
        // Get the existing auth guards
        $guards = Config::get('auth.guards', []);

        // Add the custom API guard dynamically
        $guards['apikey'] = [
            'driver' => 'apikey', // Ensure this matches your Auth::extend name
            'provider' => 'users',
        ];

        // Set the modified config
        Config::set('auth.guards', $guards);
    }
}
