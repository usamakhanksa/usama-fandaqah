<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IptvGuestNeed extends Model
{
    protected $guarded = [];
    
    public function reservation(){
        return $this->belongsTo(Reservation::class);
    }
}
