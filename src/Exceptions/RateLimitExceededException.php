<?php

namespace Deecodek\RateLimiter\Exceptions;

use Exception;
use Throwable;

class RateLimitExceededException extends Exception
{
    protected array $headers = [];

    public function __construct(
        string $message = "Too Many Requests", 
        int $code = 429, 
        ?Throwable $previous = null,
        array $headers = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function render($request)
    {
        $response = response()->json([
            'message' => $this->getMessage(),
            'retry_after' => $this->headers['X-RateLimit-Retry-After'] ?? null,
        ], $this->getCode());

        foreach ($this->headers as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}