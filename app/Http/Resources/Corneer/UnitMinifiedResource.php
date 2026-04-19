<?php

namespace App\Http\Resources\Corneer;

use App\Unit;
use Carbon\Carbon;
use App\Reservation;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\MediaLibrary\Models\Media;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitMinifiedResource extends JsonResource
{
    public $date;
    public $date_end;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // Ensure you call the parent constructor
        parent::__construct($resource);
        $this->resource = $resource;
        $this->date = Carbon::parse(request()->date_start);
        if(request()->date_end){
            $this->date_end = Carbon::parse(request()->date_end);
        }
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $date_start = Carbon::parse(request()->date_start);
        $date_end = Carbon::parse(request()->date_end);
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        return [
            'id' => $this->id,
            'hash'  =>  Hashids::encode($this->id),
            'unit_number' => $this->unit_number,
            'has_reservation' => checkIfUnitHasReservation($this->id , request()->date_start),
            'reservations_dates' => $this->getReservationsDates(),
            'unit_status' => $this->status
        ];
    }

}
