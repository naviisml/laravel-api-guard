<?php

namespace Naviisml\ApiGuard\Database\Seeders;

use Illuminate\Database\Seeder;
use Naviisml\ApiGuard\Models\ApiKey;
use Naviisml\Make\Concerns\UseEnvironment;

class ApiSeeder extends Seeder
{
    use UseEnvironment;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->runInDevelopment(function () {
            if (ApiKey::query()->wherePublicKey('development')->first()) {
                return;
            }

            ApiKey::factory()->create([
                'public_key' => 'development',
                'private_key' => 'development',
            ]);
        });

        $this->runInTesting(function () {
            if (ApiKey::query()->wherePublicKey('testing')->first()) {
                return;
            }

            ApiKey::factory()->create([
                'public_key' => 'testing',
                'private_key' => 'testing',
            ]);
        });
    }
}
