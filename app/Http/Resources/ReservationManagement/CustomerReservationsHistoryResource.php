<?php

namespace App\Http\Resources\ReservationManagement;

use App\Reservation;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class CustomerReservationsHistoryResource extends JsonResource
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
        return $this->reservation_type == 'single' ? [
            'id' => $this->id,
            'reservation_number' => $this->number,
            'reservation_type' => $this->reservation_type,
            'unit' => $this->unit ? $this->unit->unit_number . ' - ' . $this->unit->name : '-',
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'balance' => $this->getCustomerBalance($this)
        ] : [
            'id' => $this->id,
            'reservation_number' => $this->number,
            'reservation_type' => $this->reservation_type,
            'unit' => groupReservationsUnitCount($this),
            'date_in' => $this->getCalculations($this)['start_date'],
            'date_out' => $this->getCalculations($this)['end_date'],
            'balance' => shareableGroupBalance($this)
        ];
    }

    function getCustomerBalance($reservation){
        $balance = 0;
        if($reservation->reservation_type == 'single'){
            $balance =  $reservation->balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
        }else{
            $balance = shareableGroupBalance($reservation);
        }

        return $balance;
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
