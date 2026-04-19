<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class StaahDailyReservation extends Model
{
    protected $guarded = [];
    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    } 
}
