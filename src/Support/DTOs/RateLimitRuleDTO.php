<?php

namespace Deecodek\RateLimiter\Support\DTOs;

class RateLimitRuleDTO
{
    public string $name;
    public ?int $tenantId;
    public array $dimensions;
    public string $algorithm;
    public array $limits;
    public int $window;
    public int $weight;
    public int $burst;
    public int $cooldown;
    public bool $enabled;
    public int $priority;
    public ?int $inheritedFromId;
    public ?\DateTime $createdAt;
    public ?\DateTime $updatedAt;

    public function __construct(
        string $name,
        ?int $tenantId = null,
        array $dimensions = [],
        string $algorithm = 'fixed_window',
        array $limits = [],
        int $window = 60,
        int $weight = 1,
        int $burst = 0,
        int $cooldown = 0,
        bool $enabled = true,
        int $priority = 0,
        ?int $inheritedFromId = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        $this->name = $name;
        $this->tenantId = $tenantId;
        $this->dimensions = $dimensions;
        $this->algorithm = $algorithm;
        $this->limits = $limits;
        $this->window = $window;
        $this->weight = $weight;
        $this->burst = $burst;
        $this->cooldown = $cooldown;
        $this->enabled = $enabled;
        $this->priority = $priority;
        $this->inheritedFromId = $inheritedFromId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getMaxAttempts(): int
    {
        return $this->limits['max_attempts'] ?? $this->limits['limit'] ?? 60;
    }

    public function getDecayMinutes(): int
    {
        return $this->limits['decay_minutes'] ?? $this->limits['window'] ?? 1;
    }

    public function hasDimension(string $dimension): bool
    {
        return in_array($dimension, $this->dimensions);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'tenant_id' => $this->tenantId,
            'dimensions' => $this->dimensions,
            'algorithm' => $this->algorithm,
            'limits' => $this->limits,
            'window' => $this->window,
            'weight' => $this->weight,
            'burst' => $this->burst,
            'cooldown' => $this->cooldown,
            'enabled' => $this->enabled,
            'priority' => $this->priority,
            'inherited_from_id' => $this->inheritedFromId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['tenant_id'] ?? null,
            $data['dimensions'] ?? [],
            $data['algorithm'] ?? 'fixed_window',
            $data['limits'] ?? [],
            $data['window'] ?? 60,
            $data['weight'] ?? 1,
            $data['burst'] ?? 0,
            $data['cooldown'] ?? 0,
            $data['enabled'] ?? true,
            $data['priority'] ?? 0,
            $data['inherited_from_id'] ?? null,
            isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null
        );
    }

    public static function fromModel(\Deecodek\RateLimiter\Models\RateLimitRule $model): self
    {
        return new self(
            $model->name,
            $model->tenant_id,
            $model->dimensions ?? [],
            $model->algorithm,
            $model->limits ?? [],
            $model->window,
            $model->weight,
            $model->burst,
            $model->cooldown,
            $model->enabled,
            $model->priority,
            $model->inherited_from_id,
            $model->created_at,
            $model->updated_at
        );
    }
}