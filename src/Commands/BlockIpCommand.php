<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitBlock;

class BlockIpCommand extends Command
{
    protected $signature = 'rate-limit:block-ip 
                            {ip : The IP address to block}
                            {--duration= : Duration in minutes}
                            {--reason= : Reason for blocking}
                            {--permanent : Make block permanent}';

    protected $description = 'Block an IP address';

    public function handle()
    {
        $ip = $this->argument('ip');
        $duration = $this->option('duration');
        $reason = $this->option('reason') ?: 'Manual block';
        $permanent = $this->option('permanent');

        $expiresAt = null;
        if (!$permanent && $duration) {
            $expiresAt = now()->addMinutes($duration);
        }

        RateLimitBlock::create([
            'ip' => $ip,
            'reason' => $reason,
            'permanent' => $permanent,
            'expires_at' => $expiresAt,
        ]);

        $this->info("IP {$ip} has been blocked.");
    }
}