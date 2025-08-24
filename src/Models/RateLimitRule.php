<?php

namespace Deecodek\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RateLimitRule extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $casts = [
        'dimensions' => 'array',
        'limits' => 'array',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function inheritedFrom()
    {
        return $this->belongsTo(self::class, 'inherited_from_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'inherited_from_id');
    }
}