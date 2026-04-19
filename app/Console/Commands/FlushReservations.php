<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Reservation ;

class FlushReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flush:reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will update all current reservations new columns date_in_time & date_out_time cause they are needed to handle revenue tax report';

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

        if ($this->confirm('Are you sure to proceed with flushing reservations date in time & date out time ?')) {

            $reservations = Reservation::all() ;
            if($reservations){
                foreach ($reservations as $reservation){

                    /* Add-On to flush date_in_time && date_out_time new columns in reservations tbl to be used in Total Revenues, Taxes & Fees Report */
                    $day_start_time =  \App\Handlers\Settings::get('day_start');
                    $date_in = Carbon::parse($reservation->date_in)->toDateString();
                    $day_end_time =  \App\Handlers\Settings::get('day_end');
                    $date_out = Carbon::parse($reservation->date_out)->toDateString();
                    $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
                    $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

                    $reservation->date_in_time = $combinedDateInTime;
                    $reservation->date_out_time = $combinedDateOutTime;

                    $reservation->save();

                }

                $this->info('Congratulations , all reservations  date in time & date out time have been updated successfully');

            }else{
                $this->error('No reservations to update date in time & date out time');
            }


        }else{
            $this->info('Thanks and GodBye -_-');
        }



    }
}
