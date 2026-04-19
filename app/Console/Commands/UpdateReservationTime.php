<?php

namespace App\Console\Commands;

use App\Team;
use App\Setting;
use App\Reservation;
use Illuminate\Console\Command;

class UpdateReservationTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:reservation-times';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Reservation Date In Time & Date Out Time For All Tennants';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $teams = Team::whereNull('deleted_at')->get();

        if($teams){

            foreach ($teams as $team){
                
                $reservations =  Reservation::withoutGlobalScope('team_id')
                ->where('team_id' , $team->id)
                ->where('status' , '!='  , 'canceled')
                ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->get(); 

                // dd($reservations);

                if(count($reservations)){
                    foreach($reservations as $reservation){
                        $date_in = $reservation->date_in; 
                        $day_start_time = Setting::query();
                        $day_start_time = $day_start_time->whereRaw(" `key` = ? && team_id = ? ", ['day_start' ,  $team->id])->first(); 
                        $day_start_time = $day_start_time ? $day_start_time->value : '09:00';
                
                        $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
                        $date_out = $reservation->date_out; 

                        $day_end_time = Setting::query();
                        $day_end_time = $day_end_time->whereRaw(" `key` = ? && team_id = ? ", ['day_end' ,  $team->id])->first(); 
                        $day_end_time = $day_end_time ? $day_end_time->value : '18:00';
                        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));
                        
                        $reservation->date_in_time = $combinedDateInTime ; 
                        $reservation->date_out_time = $combinedDateOutTime ; 
    
                        $reservation->save();
                    }
                }
            }
        }
    }
}
