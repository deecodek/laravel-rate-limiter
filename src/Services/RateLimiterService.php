<?php

namespace Deecodek\RateLimiter\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Deecodek\RateLimiter\Models\RateLimitRule;
use Deecodek\RateLimiter\Support\Caching\CacheKeyGenerator;
use Deecodek\RateLimiter\Algorithms\AlgorithmInterface;

class RateLimiterService
{
    protected string $store;
    protected CacheKeyGenerator $keyGenerator;
    protected array $algorithms = [];

    public function __construct()
    {
        $this->store = config('rate-limiter.default_store', 'redis');
        $this->keyGenerator = new CacheKeyGenerator();
        $this->registerAlgorithms();
    }

    protected function registerAlgorithms(): void
    {
        $algorithms = config('rate-limiter.algorithms', []);
        
        foreach ($algorithms as $name => $config) {
            if (isset($config['class']) && class_exists($config['class'])) {
                $this->algorithms[$name] = app($config['class']);
            }
        }
    }

    public function attempt(Request $request, array $ruleConfig): object
    {
        // Get tenant ID if multi-tenancy is enabled
        $tenantId = $this->getTenantId($request);
        
        // Generate cache key based on dimensions
        $key = $this->keyGenerator->generate($request, $ruleConfig, $tenantId);
        
        // Get rule configuration
        $rule = $this->resolveRule($request, $ruleConfig);
        
        // Get algorithm
        $algorithm = $this->getAlgorithm($rule['algorithm'] ?? 'fixed_window');
        
        // Extract limit and window
        $limit = $rule['max_attempts'] ?? 60;
        $window = ($rule['decay_minutes'] ?? 1) * 60;
        $weight = $rule['weight'] ?? 1;
        
        // Apply rate limiting
        $result = $algorithm->attempt($key, $limit, $window, $weight);
        
        // Calculate headers
        $headers = [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $result['remaining'],
            'X-RateLimit-Reset' => $result['reset_time'],
            'X-RateLimit-Retry-After' => $result['retry_after'],
            'X-RateLimit-Policy' => $rule['algorithm'] ?? 'fixed_window',
        ];
        
        // Log attempt for analytics
        $this->logAttempt($request, $result['current'], $result['allowed'], $rule);
        
        return (object) [
            'allowed' => $result['allowed'],
            'attempts' => $result['current'],
            'headers' => $headers,
        ];
    }

    public function status(Request $request, array $ruleConfig): array
    {
        $tenantId = $this->getTenantId($request);
        $key = $this->keyGenerator->generate($request, $ruleConfig, $tenantId);
        $rule = $this->resolveRule($request, $ruleConfig);
        
        $algorithm = $this->getAlgorithm($rule['algorithm'] ?? 'fixed_window');
        $limit = $rule['max_attempts'] ?? 60;
        $window = ($rule['decay_minutes'] ?? 1) * 60;
        
        return $algorithm->status($key, $limit, $window);
    }

    protected function getAlgorithm(string $name): AlgorithmInterface
    {
        return $this->algorithms[$name] ?? $this->algorithms['fixed_window'];
    }

    protected function resolveRule(Request $request, array $ruleConfig): array
    {
        // If it's a named rule, fetch from database
        if (isset($ruleConfig['rule'])) {
            $rule = RateLimitRule::where('name', $ruleConfig['rule'])->first();
            if ($rule) {
                return [
                    'max_attempts' => $rule->limits['max_attempts'] ?? 60,
                    'decay_minutes' => $rule->limits['decay_minutes'] ?? 1,
                    'algorithm' => $rule->algorithm,
                    'weight' => $rule->weight,
                ];
            }
        }
        
        // Return the config as-is with defaults
        return array_merge([
            'max_attempts' => 60,
            'decay_minutes' => 1,
            'algorithm' => 'fixed_window',
            'weight' => 1,
        ], $ruleConfig);
    }

    protected function getTenantId(Request $request): ?int
    {
        $resolver = config('rate-limiter.tenancy.resolver');
        
        if (is_callable($resolver)) {
            return call_user_func($resolver, $request);
        }
        
        if (is_string($resolver) && class_exists($resolver)) {
            return app($resolver)->resolve($request);
        }
        
        return null;
    }

    protected function logAttempt(Request $request, int $attempts, bool $allowed, array $rule): void
    {
        // Async logging will be implemented later
        // For now, we just return
    }
}