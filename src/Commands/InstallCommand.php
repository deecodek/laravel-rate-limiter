<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'rate-limit:install 
                            {--force : Overwrite any existing files}
                            {--migrate : Run migrations}';

    protected $description = 'Install the rate limiter package';

    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'rate-limiter-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'rate-limiter-migrations',
            '--force' => $this->option('force'),
        ]);

        if ($this->option('migrate')) {
            $this->call('migrate');
        }

        $this->info('Rate limiter package installed successfully!');
    }
}