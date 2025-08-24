<?php

if (!function_exists('rate_limiter')) {
    /**
     * Get the rate limiter instance.
     *
     * @return \Deecodek\RateLimiter\Services\RateLimiterService
     */
    function rate_limiter()
    {
        return app('rate-limiter');
    }
}

if (!function_exists('rate_limiter_analytics')) {
    /**
     * Get the analytics service instance.
     *
     * @return \Deecodek\RateLimiter\Services\AnalyticsService
     */
    function rate_limiter_analytics()
    {
        return app('rate-limiter.analytics');
    }
}

if (!function_exists('rate_limiter_alerts')) {
    /**
     * Get the alerts service instance.
     *
     * @return \Deecodek\RateLimiter\Services\AlertService
     */
    function rate_limiter_alerts()
    {
        return app('rate-limiter.alerts');
    }
}

if (!function_exists('ip_matches_cidr')) {
    /**
     * Check if an IP matches a CIDR range.
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    function ip_matches_cidr($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~$wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }
}

if (!function_exists('normalize_route_pattern')) {
    /**
     * Normalize a route pattern for matching.
     *
     * @param string $route
     * @return string
     */
    function normalize_route_pattern($route)
    {
        return preg_replace('/\{[^}]+\}/', '*', $route);
    }
}