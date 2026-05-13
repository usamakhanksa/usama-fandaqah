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
use App\Services\AuditEnforcementService;

class ReservationObserver
{
    protected $enforcement;

    public function __construct(AuditEnforcementService $enforcement)
    {
        $this->enforcement = $enforcement;
    }

    public function creating(Reservation $reservation)
    {
        $teamId = $reservation->team_id ?? Auth::user()->current_team_id ?? null;
        
        // Rule: Block creating reservations with closed date_in
        if ($teamId && $reservation->date_in) {
            if ($this->enforcement->isDateClosed($teamId, $reservation->date_in) && !$this->enforcement->canBackdate()) {
                abort(403, 'Cannot create reservations for a closed business date.');
            }
        }
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
     */
    public function updating(Reservation $reservation)
    {
        $teamId = $reservation->team_id;

        // Rule: Block editing reservation prices for closed nights
        if ($reservation->isDirty(['total_price', 'prices'])) {
            if ($this->enforcement->isBackdated($teamId, $reservation->date_in) && !$this->enforcement->canBackdate()) {
                abort(403, 'Cannot edit prices for closed/past business dates.');
            }
        }

        // Rule: Block backdating check-in into closed date
        if ($reservation->isDirty('checked_in') && $reservation->checked_in) {
            if ($this->enforcement->isDateClosed($teamId, $reservation->checked_in) && !$this->enforcement->canBackdate()) {
                abort(403, 'Cannot backdate check-in into a closed business date.');
            }
        }

        // Rule: Block backdating checkout into closed date
        if ($reservation->isDirty('checked_out') && $reservation->checked_out) {
            if ($this->enforcement->isDateClosed($teamId, $reservation->checked_out) && !$this->enforcement->canBackdate()) {
                abort(403, 'Cannot backdate check-out into a closed business date.');
            }
        }

        // Rule: Block changing checked-in/checked-out fields after audit lock (if already audited)
        if ($reservation->isDirty(['checked_in', 'checked_out']) && $reservation->getOriginal('checked_in')) {
             $origDate = Carbon::parse($reservation->getOriginal('checked_in'))->format('Y-m-d');
             if ($this->enforcement->isDateClosed($teamId, $origDate) && !$this->enforcement->canBackdate()) {
                 abort(403, 'Cannot modify check-in/out status after Night Audit lock.');
             }
        }

        // Rule: Cancel locked reservation only with finance.backdate
        if ($reservation->isDirty('status') && $reservation->status === 'canceled') {
            if ($this->enforcement->isDateClosed($teamId, $reservation->date_in) && !$this->enforcement->canBackdate()) {
                abort(403, 'Special permission (finance.backdate) required to cancel a reservation from a closed date.');
            }
        }

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

    public function updated(Reservation $reservation)
    {
        if($reservation->isDirty('customer_id')){
            $new_customer_id = $reservation->customer_id;
            $old_customer_id = $reservation->getOriginal('customer_id');
            $reservation->enableLogging();

            if($new_customer_id != $old_customer_id){
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

    public function deleted(Reservation $reservation)
    {
        // Rule: Block deleting reservations from closed dates
        $teamId = $reservation->team_id;
        if ($this->enforcement->isDateClosed($teamId, $reservation->date_in) && !$this->enforcement->canBackdate()) {
            abort(403, 'Cannot delete a reservation belonging to a closed business date.');
        }

        event(new ReservationDeleted($reservation));
    }
}
