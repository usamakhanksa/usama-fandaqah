<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClientProfile extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'national_id', 'type', 'city', 'address'];

    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    public function activities(): HasMany {
        return $this->hasMany(ClientActivity::class, 'client_profile_id');
    }

    public function membership(): HasOne {
        return $this->hasOne(ClientMembership::class, 'client_profile_id');
    }

    public function sales(): HasMany {
        return $this->hasMany(ClientSale::class, 'client_profile_id');
    }

    public function scopeSearch($query, $search) {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
