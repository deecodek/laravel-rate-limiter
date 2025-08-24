<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearRateLimitsCommand extends Command
{
    protected $signature = 'rate-limit:clear 
                            {user? : The user ID to clear limits for}
                            {--ip= : The IP address to clear limits for}
                            {--route= : The route pattern to clear limits for}
                            {--all : Clear all rate limits}';

    protected $description = 'Clear rate limits for a user, IP, route, or all';

    public function handle()
    {
        if ($this->option('all')) {
            $this->clearAllLimits();
            return;
        }

        if ($userId = $this->argument('user')) {
            $this->clearUserLimits($userId);
        }

        if ($ip = $this->option('ip')) {
            $this->clearIpLimits($ip);
        }

        if ($route = $this->option('route')) {
            $this->clearRouteLimits($route);
        }

        $this->info('Rate limits cleared successfully.');
    }

    protected function clearAllLimits(): void
    {
        if (config('rate-limiter.default_store') === 'redis') {
            $keys = Redis::keys('rate_limit:*');
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
        
        $this->info('All rate limits cleared.');
    }

    protected function clearUserLimits(string $userId): void
    {
        if (config('rate-limiter.default_store') === 'redis') {
            $keys = Redis::keys("rate_limit:*user:{$userId}*");
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
        
        $this->info("Rate limits for user {$userId} cleared.");
    }

    protected function clearIpLimits(string $ip): void
    {
        if (config('rate-limiter.default_store') === 'redis') {
            $keys = Redis::keys("rate_limit:*ip:{$ip}*");
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
        
        $this->info("Rate limits for IP {$ip} cleared.");
    }

    protected function clearRouteLimits(string $route): void
    {
        if (config('rate-limiter.default_store') === 'redis') {
            $keys = Redis::keys("rate_limit:*route:{$route}*");
            if (!empty($keys)) {
                Redis::del($keys);
            }
        }
        
        $this->info("Rate limits for route {$route} cleared.");
    }
}