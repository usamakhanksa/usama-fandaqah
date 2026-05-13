<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTeam;

class Sender extends Model
{
    use HasFactory, HasTeam, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'name_ar',
        'type',
        'id_number',
        'phone',
        'email',
        'address',
        'bank_id',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }
}