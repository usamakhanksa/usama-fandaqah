<?php

namespace App\Listeners;

use App\Events\IndexUnitHandler;
use App\Reservation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class IndexUnitListener implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  IndexUnitHandler  $event
     * @return void
     */
    public function handle(IndexUnitHandler $event)
    {
        if($event->key === 'update_settings'){
            $this->updateCachedSettings($event);
        }

        if($event->team_id){
            $this->updateReservationDateOutTime($event->team_id,$event->data);
        }

    }

    protected function updateCachedSettings($event){

        Cache::forget($event->team_id);
        Cache::add($event->team_id,$event->data);

    }

    protected function updateReservationDateOutTime($team_id,$data){
       $reservations =  Reservation::withoutGlobalScope('team_id')
                    ->where('team_id' , $team_id)
                    ->where('status' , '='  , 'confirmed')
                    ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->get();

        if(count($reservations)){
            foreach($reservations as $reservation){
                $date_in = $reservation->date_in;
                $day_start_time = isset($data['day_start']) ? $data['day_start'] : "13:00";
                $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
                $date_out = $reservation->date_out;
                $day_end_time = isset($data['day_end']) ? $data['day_end'] : "12:00";
                $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

                $reservation->date_in_time = $combinedDateInTime ;
                $reservation->date_out_time = $combinedDateOutTime ;

                $reservation->save();
            }
        }
    }
}
