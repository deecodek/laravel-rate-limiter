<?php

namespace Deecodek\RateLimiter\Support\Caching;

class RedisLuaScripts
{
    public static function fixedWindowScript()
    {
        return "
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
    }
    
    public static function slidingWindowScript()
    {
        return "
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
    }
    
    public static function tokenBucketScript()
    {
        return "
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
    }
}