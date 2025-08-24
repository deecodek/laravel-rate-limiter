<?php

namespace Deecodek\RateLimiter\Support\Caching;

use Illuminate\Http\Request;

class CacheKeyGenerator
{
    public function generate(Request $request, array $ruleConfig, ?int $tenantId = null): string
    {
        $parts = [];
        
        if ($tenantId) {
            $parts[] = "tenant:{$tenantId}";
        }
        
        // Add user dimension
        if ($request->user()) {
            $parts[] = "user:{$request->user()->id}";
        }
        
        // Add IP dimension
        $parts[] = "ip:{$request->ip()}";
        
        // Add route dimension
        $parts[] = "route:" . $this->normalizeRoute($request->route()->uri ?? $request->path());
        
        // Add method dimension
        $parts[] = "method:{$request->method()}";
        
        // Add rule name if present
        if (isset($ruleConfig['rule'])) {
            $parts[] = "rule:{$ruleConfig['rule']}";
        }
        
        return 'rate_limit:' . implode('|', $parts);
    }
    
    protected function normalizeRoute(string $route): string
    {
        // Normalize route for consistent key generation
        return preg_replace('/\{[^}]+\}/', '*', $route);
    }
}