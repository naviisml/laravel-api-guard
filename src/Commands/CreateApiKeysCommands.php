<?php

namespace Naviisml\ApiGuard\Commands;

use Illuminate\Console\Command;
use Naviisml\ApiGuard\Models\ApiKey;

class CreateApiKeysCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:api-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create API keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apiKeys = ApiKey::create([
            'public_key' => 'public_key',
            'private_key' => 'private_key',
        ]);

        $apiKeys->regeneratePublicKey();
        $apiKeys->regeneratePrivateKey();
        $apiKeys->save();

        $this->info('API keys created successfully.');
        $this->info('Public key: '.$apiKeys->public_key);
        $this->info('Private key: '.$apiKeys->private_key);

        return Command::SUCCESS;
    }
}
