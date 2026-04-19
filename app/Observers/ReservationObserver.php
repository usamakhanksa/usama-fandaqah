<?php

namespace App\Observers;

use App\Unit;
use App\Activity;
use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use App\TeamCounter;
use App\SpecialPrice;
use Carbon\CarbonPeriod;
use App\ReservationTransfer;
use App\Events\ReservationDeleted;
use Illuminate\Support\Facades\Auth;
use App\Events\ReservationTransferred;
use App\Http\Resources\OffersAndSpecialPrices\ModelDatesMinifiedResource;

class ReservationObserver
{
    public function creating(Reservation $reservation)
    {

    }

    public function created(Reservation $reservation)
    {


        if(is_null(auth()->user())){

                $description = "  انشاء الحجز رقم  {$reservation->number}";
                $log = Activity::where('team_id' , 0)
                                    ->where('subject_type' , 'App\\Reservation')
                                    ->where('subject_id' , $reservation->id)
                                    ->where('description' , $description)
                                    ->latest()->first();


                if($log){
                    $log->team_id = $reservation->team_id;
                    $log->save();
                }

        }

        $reservation->disableLogging();
    }

    /**
     * Listen to the User created event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updating(Reservation $reservation)
    {
        if($reservation->isDirty('unit_id')){
            $new_unit_id = $reservation->unit_id;
            $old_unit_id = $reservation->getOriginal('unit_id');

            $reservation_tr =  ReservationTransfer::create([
                'old_unit_id' => $old_unit_id,
                'new_unit_id' => $new_unit_id,

                'created_by' => auth()->check() ? auth()->user()->id : null,
                'reservation_id' => $reservation->id,

                'old_date_in' => $reservation->getOriginal('date_in'),
                'new_date_in' => $reservation->date_in,
                'old_date_out' => $reservation->getOriginal('date_out'),
                'new_date_out' => $reservation->date_out,

                'old_price' => $reservation->getOriginal('total_price'),
                'new_price' => $reservation->total_price,
                'reservation_id' => $reservation->id,
                'reason' => request()->input('reason'),
                'team_id' => $reservation->team_id
            ]);
            event(new ReservationTransferred($reservation, $reservation_tr->old_unit->unit_number, $reservation_tr->new_unit->unit_number));
        }
    }

    /**
     * Handle the reservation "updated" event.
     *
     * @param  \App\Reservation  $reservation
     * @return void
     */
    public function updated(Reservation $reservation)
    {
        if($reservation->isDirty('customer_id')){

            $new_customer_id = $reservation->customer_id;
            $old_customer_id = $reservation->getOriginal('customer_id');
            $reservation->enableLogging();


            if($new_customer_id != $old_customer_id){
                // means customer changed
                $old_customer = Customer::find($old_customer_id);
                $new_customer = Customer::find($new_customer_id);


                $message =  !is_null($old_customer) ?   "  تغيير العميل علي الحجز رقم {$reservation->number}" :  "  تسكين العميل علي الحجز رقم {$reservation->number}" ;
                $properties = [
                    'old' => !is_null($old_customer) ?  [
                        'customer_id' => $old_customer->id,
                        'customer.name' => $old_customer->name,
                        'customer.phone' => $old_customer->phone,
                        'customer.email' => $old_customer->email,
                        'customer.customer_type_string' => $old_customer->customer_type_string,
                        'customer.id_type_string' => $old_customer->id_type_string,
                        'customer.nationality_string' => $old_customer->nationality_string,
                        'customer.id_number' => $old_customer->id_number
                    ] : [
                        'customer_id' => null,
                        'customer.name' => null,
                        'customer.phone' => null,
                        'customer.email' => null,
                        'customer.customer_type_string' => null,
                        'customer.id_type_string' => null,
                        'customer.nationality_string' => null,
                        'customer.id_number' => null,
                        'note' => 'attach_customer_on_reservation'
                    ] ,
                    'attributes' => [
                        'customer_id' => $new_customer->id,
                        'customer.name' => $new_customer->name,
                        'customer.phone' => $new_customer->phone,
                        'customer.email' => $new_customer->email,
                        'customer.customer_type_string' => $new_customer->customer_type_string,
                        'customer.id_type_string' => $new_customer->id_type_string,
                        'customer.nationality_string' => $new_customer->nationality_string,
                        'customer.id_number' => $new_customer->id_number
                    ]
                ];

                activity( !is_null($old_customer) ?  'change_reservation_customer' : 'attach_reservation_customer')
                    ->causedBy(auth()->user())
                    ->performedOn($reservation)
                    ->withProperties($properties)
                    ->log($message);


            }

        }

        if(is_null(auth()->user()) && $reservation->action_type == Reservation::ACTION_UPDATERESERVATIONFROMCOMMAND){

            // this is definitly an auto renew reservation
                $log = Activity::where('team_id' , 0)
                                    ->where('subject_type' , 'App\\Reservation')
                                    ->where('subject_id' , $reservation->id)
                                    ->where('description' , 'like' ,  '%تلقائى%')
                                    ->latest()->first();

                if($log){
                    $log->team_id = $reservation->team_id;
                    $log->save();
                }

        }
    }

    /**
     * Handle the reservation "deleted" event.
     *
     * @param  \App\Reservation  $reservation
     * @return void
     */
    public function deleted(Reservation $reservation)
    {
        event(new ReservationDeleted($reservation));
    }

    /**
     * Handle the reservation "restored" event.
     *
     * @param  \App\Reservation  $reservation
     * @return void
     */
    public function restored(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "force deleted" event.
     *
     * @param  \App\Reservation  $reservation
     * @return void
     */
    public function forceDeleted(Reservation $reservation)
    {
        //
    }
}
