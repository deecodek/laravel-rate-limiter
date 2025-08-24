<?php

namespace Deecodek\RateLimiter\Services;

use Deecodek\RateLimiter\Models\RateLimitAttempt;
use Deecodek\RateLimiter\Models\RateLimitRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getStats($start, $end)
    {
        $totalAttempts = RateLimitAttempt::whereBetween('ts', [$start, $end])->count();
        $blockedAttempts = RateLimitAttempt::whereBetween('ts', [$start, $end])
            ->where('decision', 'blocked')
            ->count();
            
        $topRoutes = RateLimitAttempt::whereBetween('ts', [$start, $end])
            ->select('route', DB::raw('count(*) as total'))
            ->groupBy('route')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
            
        $topIps = RateLimitAttempt::whereBetween('ts', [$start, $end])
            ->select('ip', DB::raw('count(*) as total'))
            ->groupBy('ip')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
            
        return [
            'period' => [
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
            ],
            'total_attempts' => $totalAttempts,
            'blocked_attempts' => $blockedAttempts,
            'allowed_attempts' => $totalAttempts - $blockedAttempts,
            'top_routes' => $topRoutes,
            'top_ips' => $topIps,
        ];
    }
    
    public function getRuleUsage($ruleName, $days = 30)
    {
        $start = Carbon::now()->subDays($days);
        
        return RateLimitAttempt::where('rule_name', $ruleName)
            ->where('ts', '>=', $start)
            ->select(
                DB::raw('DATE(ts) as date'),
                DB::raw('count(*) as attempts'),
                DB::raw('sum(case when decision = "blocked" then 1 else 0 end) as blocked')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}