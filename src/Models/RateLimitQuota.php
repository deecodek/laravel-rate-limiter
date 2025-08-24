<?php

namespace Deecodek\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RateLimitQuota extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'used' => 'integer',
        'limit' => 'integer',
        'rollover_available' => 'integer',
        'resets_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $table = 'rate_limit_quotas';

    protected $primaryKey = ['tenant_id', 'subject_type', 'subject_id', 'period_key'];
    public $incrementing = false;

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('tenant_id', $this->getAttribute('tenant_id'))
                    ->where('subject_type', $this->getAttribute('subject_type'))
                    ->where('subject_id', $this->getAttribute('subject_id'))
                    ->where('period_key', $this->getAttribute('period_key'));
    }
}