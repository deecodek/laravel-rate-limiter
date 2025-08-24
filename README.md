

```markdown
# Deecodek Rate Limiter

[![Latest Stable Version](https://poser.pugx.org/deecodek/rate-limiter/v)](//packagist.org/packages/deecodek/rate-limiter)
[![Total Downloads](https://poser.pugx.org/deecodek/rate-limiter/downloads)](//packagist.org/packages/deecodek/rate-limiter)
[![License](https://poser.pugx.org/deecodek/rate-limiter/license)](//packagist.org/packages/deecodek/rate-limiter)

An enterprise-grade rate limiting package for Laravel APIs. Provides multi-dimensional rate limiting, quotas, security features, analytics, and alerts with sub-millisecond performance using Redis.

## Features

*   **Multi-Dimensional Rate Limiting:** Apply limits based on User, IP (with CIDR support), API Token, Route pattern, Role/Group, or combinations.
*   **Multiple Algorithms:** Choose from Fixed Window, Sliding Window, Token Bucket (burst + refill), and Leaky Bucket (queue + overflow) for fine-grained control.
*   **Enterprise Quotas:** Time-based quotas (daily, weekly, monthly, custom), weighted requests, burst capacity, cooldowns, and rollover.
*   **Security & Abuse Protection:** IP allow/deny lists, device fingerprinting, DDoS detection & auto-blocking, bot detection.
*   **Analytics & Monitoring:** Full usage logs, historical analytics, performance metrics, and data export (CSV/JSON).
*   **Alerts & Notifications:** Threshold and abuse alerts via Email, Slack, Discord, SMS, Webhook with templating and escalation.
*   **Multi-Tenant Support:** Isolate rate limits and data between tenants.
*   **Dynamic Rules:** Database-backed rules that can be updated without redeploying your application.
*   **Laravel Integration:** Simple middleware and trait for easy integration.

## Requirements

*   PHP 8.0+
*   Laravel 9, 10, 11, or 12
*   Redis server (recommended for sub-millisecond performance)
*   Predis PHP client (`predis/predis`)

## Installation

1.  **Install the package:**
    Require the package using Composer:
    ```bash
    composer require deecodek/rate-limiter
    ```
    If it doesnt work try using dev mode
    ```bash
composer require deecodek/rate-limiter:dev-main
    ```

2.  **Publish Configuration & Migrations:**
    Publish the package's configuration file and migration files:
    ```bash
    php artisan vendor:publish --provider="Deecodek\RateLimiter\RateLimiterServiceProvider"
    ```
    Or use the dedicated install command:
    ```bash
    php artisan rate-limit:install
    ```

3.  **Run Migrations:**
    Execute the migrations to create the necessary database tables:
    ```bash
    php artisan migrate
    ```

4.  **Configure Environment:**
    Ensure your `.env` file has the correct Redis configuration:
    ```env
    # Example .env entries
    RATE_LIMITER_STORE=redis
    REDIS_HOST=127.0.0.1
    REDIS_PORT=6379
    REDIS_PASSWORD=null
    REDIS_DB=0
    ```
    Make sure your Redis server is running.

## Basic Usage

### Middleware

Apply rate limiting using middleware on your routes.

**Inline Limits:**
Limit 60 requests per minute.
```php
use Illuminate\Support\Facades\Route;

Route::middleware(['rate-limit:60:1'])->get('/api/data', function () {
    return response()->json(['message' => 'Data']);
});
```

**Named Rules:**
Define rules in the database and reference them by name.
```php
// First, create a rule (e.g., via a seeder or tinker)
// \Deecodek\RateLimiter\Models\RateLimitRule::create([
//     'name' => 'premium-tier',
//     'dimensions' => ['user'],
//     'algorithm' => 'token_bucket',
//     'limits' => ['max_attempts' => 1000, 'decay_minutes' => 1],
//     'burst' => 100,
//     'enabled' => true
// ]);

Route::middleware(['rate-limit:premium-tier'])->get('/api/premium', function () {
    return response()->json(['message' => 'Premium Data']);
});
```

### Headers

Successful requests will include rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1672531260
X-RateLimit-Retry-After: 0
X-RateLimit-Policy: fixed_window
```
Rate-limited requests (HTTP 429) will also include these headers.

### Trait (User Model)

Add rate limit management capabilities to your User model.

```php
<?php

namespace App\Models;

// ... other imports
use Deecodek\RateLimiter\Traits\HasRateLimits;

class User extends Authenticatable
{
    use HasRateLimits;
    // ... rest of your model
}
```

This adds methods like:
```php
$user->getRateLimit('api-rule-name');
$user->setRateLimit('api-rule-name', 5000, 3600); // 5000 attempts per hour
$user->resetQuota('daily');
$user->getRemainingQuota('api-rule-name');
$user->isRateLimited('upload-rule');
```

## Configuration

After publishing, the configuration file will be located at `config/rate-limiter.php`. This file allows you to customize cache stores, algorithms, quotas, security settings, alerts, and more.

## Artisan Commands

The package provides several helpful Artisan commands:
```bash
# Clear rate limits
php artisan rate-limit:clear {user?} {--ip=} {--route=} {--all}

# View statistics
php artisan rate-limit:stats {user?} {--detailed} {--format=table|json}

# Block/Unblock IPs
php artisan rate-limit:block-ip {ip} {--duration=} {--reason=}
php artisan rate-limit:unblock-ip {ip}

# Reset quotas
php artisan rate-limit:reset-quota {user?} {--type=daily|weekly|monthly} {--force}

# Install/Publish package assets
php artisan rate-limit:install {--force} {--migrate}
php artisan rate-limit:publish {--config} {--migrations}

# Analyze usage and export data
php artisan rate-limit:analyze {--hours=24} {--ip=} {--user=}
php artisan rate-limit:export {--start=} {--end=} {--format=csv|json}
```

## Advanced Features

*   **Dynamic Rules:** Create and modify `rate_limit_rules` records in your database. Changes take effect immediately.
*   **Multi-Tenancy:** Configure tenant resolution in `config/rate-limiter.php` to namespace all limits and data.
*   **Custom Algorithms:** Implement `\Deecodek\RateLimiter\Algorithms\AlgorithmInterface` and register it.
*   **Alerts:** Configure alert channels and templates in the config file.

## Contributing

Contributions are welcome! Please read `CONTRIBUTING.md` for details.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

MIT License

Copyright (c) 2025 Deepak Repositories

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.