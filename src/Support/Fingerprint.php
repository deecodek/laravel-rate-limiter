<?php

namespace Deecodek\RateLimiter\Support;

use Illuminate\Http\Request;

class Fingerprint
{
    public static function generate(Request $request)
    {
        if (!config('rate-limiter.security.fingerprinting.enabled', true)) {
            return null;
        }
        
        $headers = config('rate-limiter.security.fingerprinting.headers', [
            'User-Agent',
            'Accept-Language',
            'Accept-Encoding',
        ]);
        
        $data = [];
        foreach ($headers as $header) {
            $data[] = $request->header($header, '');
        }
        
        // Add IP address
        $data[] = $request->ip();
        
        return hash('sha256', implode('|', $data));
    }
    
    public static function matches(Request $request, $fingerprint)
    {
        return self::generate($request) === $fingerprint;
    }
}