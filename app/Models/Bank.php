<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTeam;

class Bank extends Model
{
    use HasFactory, HasTeam, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'name_ar',
        'code',
        'branch',
        'branch_code',
        'account_number',
        'iban',
        'account_name',
        'currency',
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
}