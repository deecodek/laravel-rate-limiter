<?php

namespace Deecodek\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RateLimitAttempt extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'ts' => 'datetime',
        'weight' => 'integer',
        'cost' => 'integer',
    ];

    public $timestamps = false;

    protected $table = 'rate_limit_attempts';
}