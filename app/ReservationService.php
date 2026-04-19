<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class ReservationService extends Model
{
    protected $with = ['user'];
    
    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
