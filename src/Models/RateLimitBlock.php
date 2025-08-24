<?php

namespace Deecodek\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RateLimitBlock extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'permanent' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $table = 'rate_limit_blocks';
}