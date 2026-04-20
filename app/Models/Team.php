<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'personal_team',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
