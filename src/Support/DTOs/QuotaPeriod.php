<?php

namespace Deecodek\RateLimiter\Support\DTOs;

class QuotaPeriod
{
    public string $key;
    public int $limit;
    public int $used;
    public int $rolloverAvailable;
    public \DateTime $resetsAt;
    public ?\DateTime $createdAt;
    public ?\DateTime $updatedAt;

    public function __construct(
        string $key,
        int $limit,
        int $used = 0,
        int $rolloverAvailable = 0,
        \DateTime $resetsAt = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        $this->key = $key;
        $this->limit = $limit;
        $this->used = $used;
        $this->rolloverAvailable = $rolloverAvailable;
        $this->resetsAt = $resetsAt ?? new \DateTime();
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getRemaining(): int
    {
        return max(0, $this->limit - $this->used);
    }

    public function isExpired(): bool
    {
        return $this->resetsAt < new \DateTime();
    }

    public function getPercentageUsed(): float
    {
        if ($this->limit === 0) {
            return 0;
        }
        
        return ($this->used / $this->limit) * 100;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'limit' => $this->limit,
            'used' => $this->used,
            'rollover_available' => $this->rolloverAvailable,
            'resets_at' => $this->resetsAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['key'],
            $data['limit'],
            $data['used'] ?? 0,
            $data['rollover_available'] ?? 0,
            isset($data['resets_at']) ? new \DateTime($data['resets_at']) : new \DateTime(),
            isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null
        );
    }
}