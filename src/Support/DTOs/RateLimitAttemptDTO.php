<?php

namespace Deecodek\RateLimiter\Support\DTOs;

class RateLimitAttemptDTO
{
    public string $key;
    public ?int $tenantId;
    public ?int $userId;
    public string $ip;
    public ?string $token;
    public string $route;
    public string $method;
    public int $weight;
    public int $cost;
    public string $decision;
    public ?string $reason;
    public \DateTime $timestamp;

    public function __construct(
        string $key,
        ?int $tenantId = null,
        ?int $userId = null,
        string $ip = '',
        ?string $token = null,
        string $route = '',
        string $method = 'GET',
        int $weight = 1,
        int $cost = 1,
        string $decision = 'allowed',
        ?string $reason = null,
        \DateTime $timestamp = null
    ) {
        $this->key = $key;
        $this->tenantId = $tenantId;
        $this->userId = $userId;
        $this->ip = $ip;
        $this->token = $token;
        $this->route = $route;
        $this->method = $method;
        $this->weight = $weight;
        $this->cost = $cost;
        $this->decision = $decision;
        $this->reason = $reason;
        $this->timestamp = $timestamp ?? new \DateTime();
    }

    public function isAllowed(): bool
    {
        return $this->decision === 'allowed';
    }

    public function isBlocked(): bool
    {
        return $this->decision === 'blocked';
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'ip' => $this->ip,
            'token' => $this->token,
            'route' => $this->route,
            'method' => $this->method,
            'weight' => $this->weight,
            'cost' => $this->cost,
            'decision' => $this->decision,
            'reason' => $this->reason,
            'timestamp' => $this->timestamp->format('Y-m-d H:i:s.u'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            $data['tenant_id'] ?? null,
            $data['user_id'] ?? null,
            $data['ip'] ?? '',
            $data['token'] ?? null,
            $data['route'] ?? '',
            $data['method'] ?? 'GET',
            $data['weight'] ?? 1,
            $data['cost'] ?? 1,
            $data['decision'] ?? 'allowed',
            $data['reason'] ?? null,
            isset($data['timestamp']) ? new \DateTime($data['timestamp']) : new \DateTime()
        );
    }

    public static function fromRequest(\Illuminate\Http\Request $request, array $context = []): self
    {
        return new self(
            $context['key'] ?? '',
            $context['tenant_id'] ?? null,
            $request->user()?->id,
            $request->ip(),
            $context['token'] ?? null,
            $request->route()?->uri ?? $request->path(),
            $request->method(),
            $context['weight'] ?? 1,
            $context['cost'] ?? 1,
            $context['decision'] ?? 'pending',
            $context['reason'] ?? null
        );
    }
}