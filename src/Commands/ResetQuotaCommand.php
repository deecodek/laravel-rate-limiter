<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitQuota;

class ResetQuotaCommand extends Command
{
    protected $signature = 'rate-limit:reset-quota 
                            {user? : The user ID to reset quota for}
                            {--type=daily : Quota type (daily|weekly|monthly)}
                            {--force : Force reset without confirmation}';

    protected $description = 'Reset user quota';

    public function handle()
    {
        $userId = $this->argument('user');
        $type = $this->option('type');
        $force = $this->option('force');

        if (!$force && !$this->confirm("Are you sure you want to reset {$type} quota?")) {
            return;
        }

        if ($userId) {
            RateLimitQuota::where([
                'subject_type' => 'user',
                'subject_id' => $userId,
                'period_key' => $this->getPeriodKey($type),
            ])->update(['used' => 0]);
            
            $this->info("Quota reset for user {$userId}");
        } else {
            RateLimitQuota::where('period_key', $this->getPeriodKey($type))->update(['used' => 0]);
            $this->info("All {$type} quotas reset");
        }
    }

    protected function getPeriodKey(string $type): string
    {
        return match($type) {
            'daily' => now()->format('Y-m-d'),
            'weekly' => now()->startOfWeek()->format('Y-m-d'),
            'monthly' => now()->format('Y-m'),
            default => now()->format('Y-m-d'),
        };
    }
}