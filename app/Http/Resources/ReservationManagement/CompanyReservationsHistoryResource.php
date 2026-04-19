<?php

namespace App\Http\Resources\ReservationManagement;

use App\Reservation;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class CompanyReservationsHistoryResource extends JsonResource
{
    public function __construct($resource)
    {
        static::$wrap = null;
        parent::__construct($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return  $this->reservation_type == 'group' ? [
            'id' => $this->id,
            'status' => $this->status,
            'reservation_number' => $this->number,
            'units_count' => groupReservationsUnitCount($this),
            'calculations' => $this->getCalculations($this),
            'balance' => shareableGroupBalance($this)
        ] : [
            'id' => $this->id,
            'status' => $this->status,
            'reservation_number' => $this->number,
            'units_count' => 1,
            'calculations' => [
                'start_date' => Carbon::parse($this->date_in)->format('Y/m/d'),
                'end_date' =>Carbon::parse($this->date_out)->format('Y/m/d'),
                'first_checked_in_date' =>  null,
                'last_checked_out_date' => null,
                'nights' => $this->nights
            ],
            'balance' => $this->balance / 100
        ];
    }

    function getCalculations($reservation)
    {
        $main_reservation = null ;
                if(is_null($reservation->attachable_id)){
                    $main_reservation = $reservation;
                    $push_main_reservation_to_collection = false;
                }else{
                    $main_reservation = Reservation::find($reservation->attachable_id);
                    $push_main_reservation_to_collection = true;
                }

                $reservations = Reservation::with('wallet','unit')
                        ->where('reservation_type' , 'group')
                        ->where('company_id' , $reservation->company_id)
                        ->where(function ($query) use($reservation,$main_reservation) {
                            return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                        })
                        ->whereIn('status' , ['confirmed' , 'canceled'])
                        // ->whereNull('checked_out')
                        ->whereNull('deleted_at')
                        ->orderBy('created_at')
                        ->get();

                if($push_main_reservation_to_collection){
                    $reservations->push($main_reservation);
                }

               return  startAndEndDateCalculatorWithNights($reservations);
    }



}
