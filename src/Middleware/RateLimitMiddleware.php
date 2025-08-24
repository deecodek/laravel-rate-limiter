<?php

namespace Deecodek\RateLimiter\Middleware;

use Closure;
use Illuminate\Http\Request;
use Deecodek\RateLimiter\Exceptions\RateLimitExceededException;
use Deecodek\RateLimiter\Services\RateLimiterService;

class RateLimitMiddleware
{
    protected RateLimiterService $rateLimiter;

    public function __construct(RateLimiterService $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
    }

    public function handle(Request $request, Closure $next, ...$limits)
    {
        // Parse limits parameter
        $rule = $this->parseLimits($limits);
        
        // Check if request should be blocked for security reasons
        if ($this->shouldBlockRequest($request)) {
            throw new RateLimitExceededException('Request blocked for security reasons');
        }

        // Apply rate limiting
        $result = $this->rateLimiter->attempt($request, $rule);
        
        if (!$result->allowed) {
            throw new RateLimitExceededException(
                'Too Many Requests', 
                429, 
                null, 
                $result->headers
            );
        }

        $response = $next($request);
        
        // Add rate limit headers
        if (config('rate-limiter.headers.enabled', true)) {
            foreach ($result->headers as $header => $value) {
                $response->header($header, $value);
            }
        }

        return $response;
    }

    protected function parseLimits(array $limits): array
    {
        // Handle named rules
        if (count($limits) === 1 && !str_contains($limits[0], ':')) {
            return ['rule' => $limits[0]];
        }

        // Handle inline rules like "1000,60"
        if (count($limits) === 1 && str_contains($limits[0], ':')) {
            [$maxAttempts, $decayMinutes] = explode(':', $limits[0]);
            return [
                'max_attempts' => (int) $maxAttempts,
                'decay_minutes' => (int) $decayMinutes,
            ];
        }

        // Handle multiple parameters
        if (count($limits) >= 2) {
            return [
                'max_attempts' => (int) $limits[0],
                'decay_minutes' => (int) $limits[1],
            ];
        }

        // Default rule
        return [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ];
    }

    protected function shouldBlockRequest(Request $request): bool
    {
        // Check IP blacklist
        $blacklistedIps = config('rate-limiter.security.ip_blacklist', []);
        $requestIp = $request->ip();
        
        foreach ($blacklistedIps as $blockedIp) {
            if ($this->ipMatches($requestIp, $blockedIp)) {
                return true;
            }
        }

        // Check if IP is in whitelist (if whitelist exists)
        $whitelistedIps = config('rate-limiter.security.ip_whitelist', []);
        if (!empty($whitelistedIps)) {
            foreach ($whitelistedIps as $allowedIp) {
                if ($this->ipMatches($requestIp, $allowedIp)) {
                    return false;
                }
            }
            return true; // Not in whitelist
        }

        return false;
    }

    protected function ipMatches(string $requestIp, string $range): bool
    {
        if (str_contains($range, '/')) {
            // CIDR notation
            [$range, $netmask] = explode('/', $range, 2);
            $rangeDecimal = ip2long($range);
            $ipDecimal = ip2long($requestIp);
            $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
            $netmaskDecimal = ~$wildcardDecimal;
            return (($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
        } else {
            // Exact match
            return $requestIp === $range;
        }
    }
}