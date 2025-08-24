<?php

namespace Deecodek\RateLimiter\Algorithms;

use Illuminate\Support\Facades\Redis;

class LeakyBucket implements AlgorithmInterface
{
    public function attempt(string $key, int $limit, int $window, int $weight = 1): array
    {
        $rate = $limit / $window; // leaks per second
        $now = microtime(true);
        
        $lua = "
            local last_level = redis.call('HGET', KEYS[1], 'level')
            local last_time = redis.call('HGET', KEYS[1], 'time')
            local capacity = tonumber(ARGV[1])
            
            if last_level == false then
                last_level = 0
                last_time = ARGV[4]
            end
            
            last_level = tonumber(last_level)
            last_time = tonumber(last_time)
            
            -- Calculate current level after leaking
            local elapsed = ARGV[4] - last_time
            local leaked = elapsed * ARGV[5]
            local current_level = math.max(0, last_level - leaked)
            
            -- Check if we can add
            if current_level + tonumber(ARGV[3]) <= capacity then
                current_level = current_level + tonumber(ARGV[3])
                redis.call('HMSET', KEYS[1], 'level', current_level, 'time', ARGV[4])
                redis.call('EXPIRE', KEYS[1], ARGV[2] * 2)
                return {1, current_level, 0}
            else
                local retry_after = (current_level + tonumber(ARGV[3]) - capacity) / ARGV[5]
                redis.call('HMSET', KEYS[1], 'level', current_level, 'time', last_time)
                redis.call('EXPIRE', KEYS[1], ARGV[2] * 2)
                return {0, current_level, math.ceil(retry_after)}
            end
        ";
        
        $result = Redis::eval(
            $lua, 
            1, 
            $key, 
            $limit,           // ARGV[1] - bucket capacity
            $window * 2,      // ARGV[2] - expiration (double window)
            $weight,          // ARGV[3] - drops to add
            $now,            // ARGV[4] - current time
            $rate            // ARGV[5] - leak rate
        );
        
        $allowed = (bool) $result[0];
        $currentLevel = $result[1];
        $retryAfter = $result[2];
        
        $remaining = max(0, $limit - (int) $currentLevel);
        
        return [
            'allowed' => $allowed,
            'remaining' => $remaining,
            'reset_time' => time() + ($retryAfter > 0 ? $retryAfter : $window),
            'retry_after' => $retryAfter,
            'current' => (int) $currentLevel,
        ];
    }

    public function status(string $key, int $limit, int $window): array
    {
        $rate = $limit / $window;
        $now = microtime(true);
        
        $lastLevel = Redis::hget($key, 'level');
        $lastTime = Redis::hget($key, 'time');
        
        if ($lastLevel === null) {
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset_time' => time() + $window,
                'retry_after' => 0,
                'current' => 0,
            ];
        }
        
        $elapsed = $now - (float) $lastTime;
        $leaked = $elapsed * $rate;
        $currentLevel = max(0, (float) $lastLevel - $leaked);
        
        $allowed = $currentLevel < $limit;
        $remaining = (int) ($limit - $currentLevel);
        $retryAfter = $currentLevel >= $limit ? 1 / $rate : 0;
        
        return [
            'allowed' => $allowed,
            'remaining' => max(0, $remaining),
            'reset_time' => time() + ($retryAfter > 0 ? ceil($retryAfter) : $window),
            'retry_after' => (int) ceil($retryAfter),
            'current' => (int) $currentLevel,
        ];
    }

    public function reset(string $key): void
    {
        Redis::del($key);
    }
}