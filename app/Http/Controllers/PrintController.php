<?php

namespace App\Http\Controllers;

use App\Team;
use App\Reservation;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class PrintController extends Controller
{

    public function groupReservationGuestList(Request $request,$reservation_id)
    {
        $decode = Hashids::decode($reservation_id);
        $reservation_id = reset($decode);
        $reservation = Reservation::with('company','customer','unit')->find($reservation_id);
        $push_main_reservation_to_collection = false;
        if($reservation){

            $main_reservation = null ;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }


            if($main_reservation->status == 'canceled'){
                $reservations = Reservation::with('customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'canceled')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }else{
                $reservations = Reservation::with('customer')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'confirmed')
                ->whereNull('deleted_at')
                ->orderBy('created_at')
                ->get();
            }

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            $customers = [];
            if(count($reservations)){
                foreach($reservations as $reservationObject){
                    $customers [] = $reservationObject->customer;
                }
            }

        }

        $customers = [];
        if (count($reservations)) {
            foreach ($reservations as $reservationObject) {
                if ($reservationObject->customer && $reservationObject->unit) {
                    $customers[] = [
                        'id' =>  $reservationObject->customer->id,
                        'name' => $reservationObject->customer->name,
                        'id_number' => $reservationObject->customer->id_number,
                        'country_id' => $reservationObject->customer->nationality_string,
                        'unit_number' => optional($reservationObject->unit)->unit_number
                    ];
                }
            }
        }
        

        return view('print.guests-list')->with(
            [
                'main_reservation' => $main_reservation,
                'contract_number' => $main_reservation ? $main_reservation->number : null, 
                'customers' => $customers , 
                'locale' => app()->getLocale(),
                'team' => auth()->user()->currentTeam,
                'dates_calculations' => startAndEndDateCalculatorWithNightsForGroupContract($reservations),
            ]
        );
    }

}
