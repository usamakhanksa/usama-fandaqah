<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasTeam;

    protected $fillable = [
        'team_id',
        'results'
    ];

    protected $casts = [
        'results'   =>  'array',
    ];
}
