<?php

namespace App\Http\Resources\Index;

use App\Reservation;
use App\Handlers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        // $day_start_time = Settings::get('day_start');
        // $day_end_time = Settings::get('day_end');

        return [
            'id' => $this->id,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'rent_type' => $this->rent_type,
            'checked_in' => $this->checked_in ,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            'nights' => $this->nights,
            'balance' => $this->wallet->balance,
            'decimal_places' => $this->wallet->decimal_places,
            'number' => $this->number,
            'reservation_type' => $this->reservation_type,
            'group_balance' => $this->reservation_type == 'group' ? $this->calculateGroupBalance($this) : $this->wallet->decimal_places,
            'company' => $this->company
            // 'group_reservation' => $this->group_reservation,
            // 'unit_id' => $this->unit_id,
            // 'date_out_and_day_start' => strtotime(date('Y-m-d H:i:s', strtotime("$this->date_out $day_start_time"))),
            // 'date_out_and_day_end' => strtotime(date('Y-m-d H:i:s', strtotime("$this->date_out $day_end_time"))),
        ];
    }

    function calculateGroupBalance($reservation)
    {
        $balances = [];
        $main_reservation = null;
        if(is_null($reservation->attachable_id)){
            $main_reservation = $reservation;
            $push_main_reservation_to_collection = false;
        }else{
            $main_reservation = Reservation::find($reservation->attachable_id);
            $push_main_reservation_to_collection = true;
        }

        $reservations = Reservation::with('wallet')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'confirmed')
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();

        if($push_main_reservation_to_collection){
            $reservations->push($main_reservation);
        }

        foreach($reservations as $reservationObject){
            $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
        }
        return array_sum($balances);
    }


}
