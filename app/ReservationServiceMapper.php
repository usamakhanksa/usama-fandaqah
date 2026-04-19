<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReservationServiceMapper extends Model
{
    //
    protected $guarded = [];

    protected $with = ['service'];

    public function service(){
        return $this->belongsTo(ReservationService::class , 'reservation_service_id' , 'id');
    }
}
