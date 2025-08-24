<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;

class PublishCommand extends Command
{
    protected $signature = 'rate-limit:publish 
                            {--config : Publish config file}
                            {--migrations : Publish migration files}
                            {--views : Publish view files}
                            {--assets : Publish asset files}';

    protected $description = 'Publish rate limiter package assets';

    public function handle()
    {
        if ($this->option('config') || !any_options()) {
            $this->call('vendor:publish', [
                '--tag' => 'rate-limiter-config',
                '--force' => true,
            ]);
        }
        
        if ($this->option('migrations') || !any_options()) {
            $this->call('vendor:publish', [
                '--tag' => 'rate-limiter-migrations',
                '--force' => true,
            ]);
        }
        
        if ($this->option('views')) {
            $this->call('vendor:publish', [
                '--tag' => 'rate-limiter-views',
                '--force' => true,
            ]);
        }
        
        if ($this->option('assets')) {
            $this->call('vendor:publish', [
                '--tag' => 'rate-limiter-assets',
                '--force' => true,
            ]);
        }
        
        $this->info('Rate limiter assets published successfully!');
    }
}

function any_options()
{
    return false; // This is a placeholder - in real implementation, check if any options were passed
}