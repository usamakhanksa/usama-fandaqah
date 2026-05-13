<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTeam;

class PaymentMethod extends Model
{
    use HasFactory;
    use HasTeam;

    protected $fillable = [
        'team_id',
        'name',
        'active',
    ];

    protected $casts = [
        'name' => 'array', // Cast JSON to array
        'active' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}