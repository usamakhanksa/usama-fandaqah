<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupReservation extends Model
{
    use SoftDeletes;
    protected $fillable = [];

    protected $casts = [
        'data' => 'array'
    ];

    protected $appends = ['hash_id'];

    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class , 'group_reservation_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Set of calculations needed for company live invoice
     *
     * @return void
     */
    public function startAndEndDateCalculatorWithNights()
    {
        $start  = Carbon::parse($this->reservations()->orderBy('date_in','asc')->first()->date_in);
        $end    = Carbon::parse($this->reservations()->orderBy('date_out','desc')->first()->date_out);
        $nights = $end->diffInDays($start);
        return [
            'start_date' => $start->format('Y/m/d'),
            'end_date' => $end->format('Y/m/d'),
            'nights' => $nights
        ];
    }
}
