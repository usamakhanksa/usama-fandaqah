<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemInterface extends Model {
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'provider', 'type', 'status', 'api_endpoint', 'config_keys', 'last_sync_at'];
    protected $casts = [
        'config_keys' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function scopeSearch($query, $search) {
        return $query->where('name', 'like', "%{$search}%")->orWhere('provider', 'like', "%{$search}%");
    }
}
