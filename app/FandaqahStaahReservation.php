<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class FandaqahStaahReservation extends Model
{
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope(new TeamScope());
    // }
    protected $guarded = [];
}
