<?php

namespace Deecodek\RateLimiter\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RateLimitExceeded
{
    use Dispatchable, SerializesModels;

    public $request;
    public $rule;
    public $attempts;

    public function __construct($request, $rule, $attempts)
    {
        $this->request = $request;
        $this->rule = $rule;
        $this->attempts = $attempts;
    }
}