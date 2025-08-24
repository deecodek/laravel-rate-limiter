<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitAttempt;
use Deecodek\RateLimiter\Models\RateLimitRule;

class RateLimitStatsCommand extends Command
{
    protected $signature = 'rate-limit:stats 
                            {user? : The user ID to get stats for}
                            {--detailed : Show detailed stats}
                            {--format=table : Output format (table|json)}';

    protected $description = 'Show rate limit statistics';

    public function handle()
    {
        $userId = $this->argument('user');
        $detailed = $this->option('detailed');
        $format = $this->option('format');

        if ($userId) {
            $this->showUserStats($userId, $detailed, $format);
        } else {
            $this->showGlobalStats($detailed, $format);
        }
    }

    protected function showUserStats($userId, $detailed, $format)
    {
        $this->info("Rate limit stats for user: {$userId}");
        
        if ($format === 'json') {
            $this->output->writeln(json_encode([
                'user_id' => $userId,
                'total_attempts' => RateLimitAttempt::where('user_id', $userId)->count(),
            ], JSON_PRETTY_PRINT));
        } else {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Attempts', RateLimitAttempt::where('user_id', $userId)->count()],
                ]
            );
        }
    }

    protected function showGlobalStats($detailed, $format)
    {
        $this->info("Global rate limit stats");
        
        $totalAttempts = RateLimitAttempt::count();
        $totalRules = RateLimitRule::count();
        
        if ($format === 'json') {
            $this->output->writeln(json_encode([
                'total_attempts' => $totalAttempts,
                'total_rules' => $totalRules,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Attempts', $totalAttempts],
                    ['Total Rules', $totalRules],
                ]
            );
        }
    }
}