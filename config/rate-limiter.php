<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    */

    'default_store' => env('RATE_LIMITER_STORE', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    */

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],
        
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
        
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
        
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dimensions
    |--------------------------------------------------------------------------
    */

    'dimensions' => [
        'enabled' => [
            'user',
            'ip',
            'token',
            'route',
            'role',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Algorithms
    |--------------------------------------------------------------------------
    */

    'algorithms' => [
        'default' => 'token_bucket',
        
        'fixed_window' => [
            'class' => \Deecodek\RateLimiter\Algorithms\FixedWindow::class,
        ],
        
        'sliding_window' => [
            'class' => \Deecodek\RateLimiter\Algorithms\SlidingWindow::class,
        ],
        
        'token_bucket' => [
            'class' => \Deecodek\RateLimiter\Algorithms\TokenBucket::class,
        ],
        
        'leaky_bucket' => [
            'class' => \Deecodek\RateLimiter\Algorithms\LeakyBucket::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quotas
    |--------------------------------------------------------------------------
    */

    'quotas' => [
        'periods' => [
            'daily' => 86400,
            'weekly' => 604800,
            'monthly' => 2592000,
        ],
        
        'rollover' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    'security' => [
        'ip_whitelist' => [],
        'ip_blacklist' => [],
        'fingerprinting' => [
            'enabled' => true,
            'headers' => [
                'User-Agent',
                'Accept-Language',
                'Accept-Encoding',
            ],
        ],
        'ddos' => [
            'thresholds' => [
                'requests_per_minute' => 1000,
                'burst_threshold' => 50,
            ],
        ],
        'bot' => [
            'heuristics' => [
                'user_agent_patterns' => [
                    'bot',
                    'crawler',
                    'spider',
                ],
            ],
        ],
        'anomaly' => [
            'sensitivity' => 0.7,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    'alerts' => [
        'channels' => [
            'mail' => [
                'enabled' => true,
                'to' => env('RATE_LIMIT_ALERT_EMAIL'),
            ],
            'slack' => [
                'enabled' => false,
                'webhook_url' => env('SLACK_WEBHOOK_URL'),
            ],
            'discord' => [
                'enabled' => false,
                'webhook_url' => env('DISCORD_WEBHOOK_URL'),
            ],
            'sms' => [
                'enabled' => false,
                'to' => env('SMS_ALERT_NUMBER'),
            ],
            'webhook' => [
                'enabled' => false,
                'url' => env('ALERT_WEBHOOK_URL'),
            ],
        ],
        
        'templates' => [
            'threshold' => 'Rate limit approaching for {dimension}',
            'abuse' => 'Potential abuse detected from {dimension}',
        ],
        
        'escalation' => [
            'levels' => [
                'warning' => [
                    'threshold' => 80,
                    'channels' => ['mail'],
                ],
                'critical' => [
                    'threshold' => 95,
                    'channels' => ['mail', 'slack'],
                ],
            ],
            'cooldown' => 300, // 5 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenancy
    |--------------------------------------------------------------------------
    */

    'tenancy' => [
        'resolver' => null,
        'namespacing' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    */

    'retention' => [
        'days' => [
            'attempts' => 30,
            'analytics' => 90,
            'alerts' => 365,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export
    |--------------------------------------------------------------------------
    */

    'export' => [
        'max_rows' => 10000,
        'formats' => ['csv', 'json'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'enabled' => true,
    ],
];