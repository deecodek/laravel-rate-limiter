<?php

namespace Deecodek\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RateLimitAlert extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'rate_limit_alerts';
}