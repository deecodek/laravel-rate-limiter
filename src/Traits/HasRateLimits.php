<?php

namespace Deecodek\RateLimiter\Traits;

use Deecodek\RateLimiter\Models\RateLimitQuota;
use Deecodek\RateLimiter\Models\RateLimitRule;
use Carbon\Carbon;

trait HasRateLimits
{
    public function getRateLimit(string $ruleName)
    {
        return RateLimitRule::where('name', $ruleName)->first();
    }

    public function setRateLimit(string $ruleName, int $limit, int $window)
    {
        return RateLimitRule::updateOrCreate(
            ['name' => $ruleName],
            [
                'limits' => ['max_attempts' => $limit, 'decay_minutes' => $window],
                'algorithm' => 'fixed_window',
                'enabled' => true,
            ]
        );
    }

    public function resetQuota(string $type = 'daily')
    {
        $periodKey = $this->getPeriodKey($type);
        
        return RateLimitQuota::updateOrCreate(
            [
                'tenant_id' => $this->getTenantId(),
                'subject_type' => 'user',
                'subject_id' => $this->id,
                'period_key' => $periodKey,
            ],
            [
                'used' => 0,
                'resets_at' => $this->getNextResetTime($type),
            ]
        );
    }

    public function getRemainingQuota(string $ruleName)
    {
        $rule = $this->getRateLimit($ruleName);
        if (!$rule) {
            return 0;
        }
        
        $periodKey = $this->getPeriodKey('daily');
        $quota = RateLimitQuota::where([
            'tenant_id' => $this->getTenantId(),
            'subject_type' => 'user',
            'subject_id' => $this->id,
            'period_key' => $periodKey,
        ])->first();
        
        if (!$quota) {
            return $rule->limits['max_attempts'] ?? 0;
        }
        
        return max(0, ($rule->limits['max_attempts'] ?? 0) - $quota->used);
    }

    public function isRateLimited(string $ruleName)
    {
        $remaining = $this->getRemainingQuota($ruleName);
        return $remaining <= 0;
    }

    public function getRateLimitHistory(string $ruleName, int $hours = 24)
    {
        // Implementation would fetch historical data
        return [];
    }

    protected function getPeriodKey(string $type): string
    {
        $now = Carbon::now();
        
        return match($type) {
            'daily' => $now->format('Y-m-d'),
            'weekly' => $now->startOfWeek()->format('Y-m-d'),
            'monthly' => $now->format('Y-m'),
            default => $now->format('Y-m-d'),
        };
    }

    protected function getNextResetTime(string $type)
    {
        $now = Carbon::now();
        
        return match($type) {
            'daily' => $now->copy()->addDay()->startOfDay(),
            'weekly' => $now->copy()->addWeek()->startOfWeek(),
            'monthly' => $now->copy()->addMonth()->startOfMonth(),
            default => $now->copy()->addDay()->startOfDay(),
        };
    }

    protected function getTenantId(): ?int
    {
        $resolver = config('rate-limiter.tenancy.resolver');
        
        if (is_callable($resolver)) {
            return call_user_func($resolver, $this);
        }
        
        return null;
    }
}