<?php

namespace Deecodek\RateLimiter\Algorithms;

use Illuminate\Support\Facades\Redis;

class TokenBucket implements AlgorithmInterface
{
    public function attempt(string $key, int $limit, int $window, int $weight = 1): array
    {
        $rate = $limit / $window; // tokens per second
        $now = microtime(true);
        
        $lua = "
            local last_tokens = redis.call('HGET', KEYS[1], 'tokens')
            local last_time = redis.call('HGET', KEYS[1], 'time')
            local burst = tonumber(ARGV[1])
            
            if last_tokens == false then
                last_tokens = burst
                last_time = ARGV[4]
            end
            
            last_tokens = tonumber(last_tokens)
            last_time = tonumber(last_time)
            
            -- Calculate tokens to add
            local elapsed = ARGV[4] - last_time
            local tokens_to_add = elapsed * ARGV[5]
            local current_tokens = math.min(burst, last_tokens + tokens_to_add)
            
            -- Check if we can consume
            if current_tokens >= tonumber(ARGV[3]) then
                current_tokens = current_tokens - tonumber(ARGV[3])
                redis.call('HMSET', KEYS[1], 'tokens', current_tokens, 'time', ARGV[4])
                redis.call('EXPIRE', KEYS[1], ARGV[2] * 2) -- Double window for safety
                return {1, current_tokens, 0}
            else
                local retry_after = (tonumber(ARGV[3]) - current_tokens) / ARGV[5]
                redis.call('HMSET', KEYS[1], 'tokens', current_tokens, 'time', last_time)
                redis.call('EXPIRE', KEYS[1], ARGV[2] * 2)
                return {0, current_tokens, math.ceil(retry_after)}
            end
        ";
        
        $result = Redis::eval(
            $lua, 
            1, 
            $key, 
            $limit,           // ARGV[1] - burst capacity
            $window * 2,      // ARGV[2] - expiration (double window)
            $weight,          // ARGV[3] - tokens to consume
            $now,            // ARGV[4] - current time
            $rate            // ARGV[5] - token refill rate
        );
        
        $allowed = (bool) $result[0];
        $remaining = $result[1];
        $retryAfter = $result[2];
        
        return [
            'allowed' => $allowed,
            'remaining' => max(0, (int) $remaining),
            'reset_time' => time() + ($retryAfter > 0 ? $retryAfter : $window),
            'retry_after' => $retryAfter,
            'current' => $limit - (int) $remaining,
        ];
    }

    public function status(string $key, int $limit, int $window): array
    {
        $rate = $limit / $window;
        $now = microtime(true);
        
        $lastTokens = Redis::hget($key, 'tokens');
        $lastTime = Redis::hget($key, 'time');
        
        if ($lastTokens === null) {
            return [
                'allowed' => true,
                'remaining' => $limit,
                'reset_time' => time() + $window,
                'retry_after' => 0,
                'current' => 0,
            ];
        }
        
        $elapsed = $now - (float) $lastTime;
        $tokensToAdd = $elapsed * $rate;
        $currentTokens = min($limit, (float) $lastTokens + $tokensToAdd);
        
        $allowed = $currentTokens > 0;
        $remaining = (int) $currentTokens;
        $retryAfter = $currentTokens >= 1 ? 0 : (1 - $currentTokens) / $rate;
        
        return [
            'allowed' => $allowed,
            'remaining' => max(0, $remaining),
            'reset_time' => time() + ($retryAfter > 0 ? ceil($retryAfter) : $window),
            'retry_after' => (int) ceil($retryAfter),
            'current' => $limit - $remaining,
        ];
    }

    public function reset(string $key): void
    {
        Redis::del($key);
    }
}