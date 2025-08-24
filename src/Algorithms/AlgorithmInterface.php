<?php

namespace Deecodek\RateLimiter\Algorithms;

interface AlgorithmInterface
{
    /**
     * Attempt to consume a request/token
     *
     * @param string $key Cache key
     * @param int $limit Maximum allowed requests
     * @param int $window Time window in seconds
     * @param int $weight Weight of this request
     * @return array Contains 'allowed', 'remaining', 'reset_time', 'retry_after'
     */
    public function attempt(string $key, int $limit, int $window, int $weight = 1): array;

    /**
     * Get current status without consuming
     *
     * @param string $key Cache key
     * @param int $limit Maximum allowed requests
     * @param int $window Time window in seconds
     * @return array Contains 'allowed', 'remaining', 'reset_time', 'retry_after'
     */
    public function status(string $key, int $limit, int $window): array;

    /**
     * Reset the counter for a key
     *
     * @param string $key Cache key
     * @return void
     */
    public function reset(string $key): void;
}