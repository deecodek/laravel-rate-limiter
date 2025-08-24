<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitBlock;

class UnblockIpCommand extends Command
{
    protected $signature = 'rate-limit:unblock-ip {ip : The IP address to unblock}';

    protected $description = 'Unblock an IP address';

    public function handle()
    {
        $ip = $this->argument('ip');

        RateLimitBlock::where('ip', $ip)->delete();

        $this->info("IP {$ip} has been unblocked.");
    }
}