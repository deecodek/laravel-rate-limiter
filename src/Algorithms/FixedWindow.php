<?php

namespace Deecodek\RateLimiter\Algorithms;

use Illuminate\Support\Facades\Redis;

class FixedWindow implements AlgorithmInterface
{
    public function attempt(string $key, int $limit, int $window, int $weight = 1): array
    {
        $lua = "
            local current = redis.call('GET', KEYS[1])
            if current == false then
                redis.call('SET', KEYS[1], ARGV[3])
                redis.call('EXPIRE', KEYS[1], ARGV[2])
                return {ARGV[3], ARGV[2]}
            end
            
            if tonumber(current) + tonumber(ARGV[3]) > tonumber(ARGV[1]) then
                return {tonumber(current), redis.call('TTL', KEYS[1])}
            end
            
            local incremented = redis.call('INCRBY', KEYS[1], ARGV[3])
            local ttl = redis.call('TTL', KEYS[1])
            if ttl == -1 then
                redis.call('EXPIRE', KEYS[1], ARGV[2])
                ttl = ARGV[2]
            end
            
            return {tonumber(incremented), ttl}
        ";
        
        $result = Redis::eval($lua, 1, $key, $limit, $window, $weight);
        
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
        $current = Redis::get($key) ?? 0;
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
            'current' => (int) $current,
        ];
    }

    public function reset(string $key): void
    {
        Redis::del($key);
    }
}