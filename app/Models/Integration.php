<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Integration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'name_ar',
        'slug',
        'integration_type',
        'provider',
        'base_url',
        'api_key',
        'api_secret',
        'config',
        'is_active',
        'last_sync_at',
        'last_sync_status',
        'last_error',
        'sync_frequency',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(IntegrationSetting::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IntegrationLog::class);
    }

    public function formIntegrations(): HasMany
    {
        return $this->hasMany(FormIntegration::class);
    }
}
