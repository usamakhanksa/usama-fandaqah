<?php

namespace App;

use App\Traits\HasTeam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;

class Occupied extends Model
{
	use HasTeam, SoftDeletes;

    protected $fillable = [
    	'units_count',
        'available',
        'occupied',
        'booked',
    	'maintenance',
    	'team_id',
    	'percentage'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }
}
