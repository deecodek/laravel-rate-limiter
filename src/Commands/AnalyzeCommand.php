<?php

namespace Deecodek\RateLimiter\Commands;

use Illuminate\Console\Command;
use Deecodek\RateLimiter\Models\RateLimitAttempt;
use Deecodek\RateLimiter\Models\RateLimitBlock;
use Illuminate\Support\Carbon;

class AnalyzeCommand extends Command
{
    protected $signature = 'rate-limit:analyze 
                            {--hours=24 : Number of hours to analyze}
                            {--ip= : Analyze specific IP}
                            {--user= : Analyze specific user}';

    protected $description = 'Analyze rate limit patterns and potential abuse';

    public function handle()
    {
        $hours = $this->option('hours');
        $ip = $this->option('ip');
        $userId = $this->option('user');
        
        $startTime = Carbon::now()->subHours($hours);
        
        $this->info("Analyzing rate limit data from {$startTime->toDateTimeString()} to now");
        
        if ($ip) {
            $this->analyzeIp($ip, $startTime);
        } elseif ($userId) {
            $this->analyzeUser($userId, $startTime);
        } else {
            $this->analyzeGlobal($startTime);
        }
    }
    
    protected function analyzeIp($ip, $startTime)
    {
        $attempts = RateLimitAttempt::where('ip', $ip)
            ->where('ts', '>=', $startTime)
            ->count();
            
        $blocks = RateLimitBlock::where('ip', $ip)
            ->where('created_at', '>=', $startTime)
            ->count();
            
        $this->table(
            ['Metric', 'Value'],
            [
                ['IP Address', $ip],
                ['Total Attempts', $attempts],
                ['Blocks', $blocks],
                ['Avg Requests/Hour', round($attempts / $this->option('hours'), 2)],
            ]
        );
    }
    
    protected function analyzeUser($userId, $startTime)
    {
        $attempts = RateLimitAttempt::where('user_id', $userId)
            ->where('ts', '>=', $startTime)
            ->count();
            
        $this->table(
            ['Metric', 'Value'],
            [
                ['User ID', $userId],
                ['Total Attempts', $attempts],
                ['Avg Requests/Hour', round($attempts / $this->option('hours'), 2)],
            ]
        );
    }
    
    protected function analyzeGlobal($startTime)
    {
        $totalAttempts = RateLimitAttempt::where('ts', '>=', $startTime)->count();
        $uniqueIps = RateLimitAttempt::where('ts', '>=', $startTime)->distinct('ip')->count('ip');
        $blocks = RateLimitBlock::where('created_at', '>=', $startTime)->count();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Attempts', $totalAttempts],
                ['Unique IPs', $uniqueIps],
                ['Blocks', $blocks],
                ['Avg Requests/Hour', round($totalAttempts / $this->option('hours'), 2)],
            ]
        );
    }
}