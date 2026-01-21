<?php

namespace Naviisml\ApiGuard;

use Naviisml\Package\Commands\InstallCommand;
use Naviisml\Package\Package;
use Naviisml\Package\PackageServiceProvider;

class ApiServiceProvider extends PackageServiceProvider
{
    /*
     * This class is a Package Service Provider
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-api-guard')
            ->hasCommands(
                Commands\CreateApiKeysCommands::class,
            )
            ->hasObservers([
                Models\ApiKey::class => [
                    Observers\ApiKeyObserver::class,
                ],
                Models\RateLimiter::class => [
                    Observers\RateLimiterObserver::class,
                ],
            ])
            ->hasSeeder(
                Database\Seeders\ApiSeeder::class,
            )
            ->runsSeeders()
            ->hasMigrations([
                '2024_06_07_114311_create_api_keys_table',
            ])
            ->runsMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToStarRepoOnGitHub('naviisml/laravel-api-guard');
            });
    }

    /**
     * Register the package services.
     */
    public function packageRegistered()
    {
        $this->app->bind(
            abstract: Contracts\ApiKeyContract::class,
            concrete: Models\ApiKey::class,
        );

        $this->app->bind(
            abstract: Contracts\RateLimiterContract::class,
            concrete: Models\RateLimiter::class,
        );
    }

    /**
     * Boot the package services.
     */
    public function packageBooted()
    {
        $router = $this->app->make(
            \Illuminate\Routing\Router::class
        );

        $router->aliasMiddleware(
            'ratelimiter',
            Middleware\ApiRateLimiter::class
        );
    }
}
