<?php

namespace Deecodek\RateLimiter\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RateLimitApproaching
{
    use Dispatchable, SerializesModels;

    public $request;
    public $rule;
    public $attempts;
    public $percentage;

    public function __construct($request, $rule, $attempts, $percentage)
    {
        $this->request = $request;
        $this->rule = $rule;
        $this->attempts = $attempts;
        $this->percentage = $percentage;
    }
}