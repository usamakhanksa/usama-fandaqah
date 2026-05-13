<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiConsumer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'allowed_ips',
        'allowed_endpoints',
        'rate_limit_per_minute',
        'is_active',
        'last_access_at',
        'request_count',
        'created_by',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'allowed_endpoints' => 'array',
        'is_active' => 'boolean',
        'last_access_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
