<?php

namespace Deecodek\RateLimiter\Algorithms;

use Illuminate\Support\Facades\Redis;

class SlidingWindow implements AlgorithmInterface
{
    public function attempt(string $key, int $limit, int $window, int $weight = 1): array
    {
        $now = microtime(true);
        $windowStart = $now - $window;
        
        $lua = "
            -- Remove old entries
            redis.call('ZREMRANGEBYSCORE', KEYS[1], 0, ARGV[4])
            
            -- Count current requests
            local current = redis.call('ZCARD', KEYS[1])
            
            -- Check if allowed
            if current + tonumber(ARGV[3]) > tonumber(ARGV[1]) then
                local ttl = redis.call('TTL', KEYS[1])
                if ttl == -1 then
                    ttl = ARGV[2]
                elseif ttl == -2 then
                    ttl = 0
                end
                return {current, ttl}
            end
            
            -- Add new request(s)
            for i = 1, tonumber(ARGV[3]) do
                redis.call('ZADD', KEYS[1], ARGV[5] + (i-1)/1000000, ARGV[5] + (i-1)/1000000)
            end
            
            -- Set expiration
            redis.call('EXPIRE', KEYS[1], ARGV[2])
            
            local new_count = redis.call('ZCARD', KEYS[1])
            return {new_count, ARGV[2]}
        ";
        
        $result = Redis::eval($lua, 1, $key, $limit, $window, $weight, $windowStart, $now);
        
        $current = $result[0];
        $ttl = $result[1];
        
        $allowed = $current <= $limit;
        $remaining = max(0, $limit - $current);
        
        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_time' => time() + $ttl,
            'retry_after' => $allowed ? 0 : $ttl,
            'current' => $current,
        ];
    }

    public function status(string $key, int $limit, int $window): array
    {
        $now = microtime(true);
        $windowStart = $now - $window;
        
        // Remove old entries
        Redis::zremrangebyscore($key, 0, $windowStart);
        
        // Count current requests
        $current = Redis::zcard($key);
        $ttl = Redis::ttl($key);
        if ($ttl == -1) {
            $ttl = $window;
        } elseif ($ttl == -2) {
            $ttl = 0;
        }
        
        $allowed = $current < $limit;
        $remaining = max(0, $limit - $current);
        
        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_time' => time() + $ttl,
            'retry_after' => $allowed ? 0 : $ttl,
            'current' => $current,
        ];
    }

    public function reset(string $key): void
    {
        Redis::del($key);
    }
}