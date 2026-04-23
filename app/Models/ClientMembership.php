<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientMembership extends Model {
    use HasFactory;
    protected $fillable = ['client_profile_id', 'tier', 'points', 'joined_at', 'expires_at'];
    protected $casts = ['joined_at' => 'datetime', 'expires_at' => 'datetime'];

    public function profile(): BelongsTo {
        return $this->belongsTo(ClientProfile::class, 'client_profile_id');
    }
}
