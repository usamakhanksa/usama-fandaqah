<?php

namespace App\Console\Commands;

use App\Reservation;
use App\TeamCounter;
use App\ReservationInvoice;
use Illuminate\Console\Command;

class CheckinCheckoutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkin-checkout:reservations {team_id} {ids*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command will checkin checkout reservations';

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
        $team_id =$this->argument('team_id');
        $ids = explode(',' , $this->argument('ids')[0]);
        // $reservations = Reservation::where('team_id' , $team_id)
        // ->whereNull('checked_in')                
        // ->withTrashed()->get();
        // if(count($reservations)){
        //     foreach ($reservations as $reservation) {
        //        $reservation->checked_in = $reservation->date_in_time;
        //        $reservation->checked_out = $reservation->date_out_time;
        //        $reservation->save();
        //     }
        // }


        $reservations = Reservation::with('invoices')->whereIn('id' , $ids)->where('team_id',$team_id)->get();
        
        if(count($reservations)){
            foreach($reservations as $reservation){
                if(count($reservation->invoices)){
                    foreach ($reservation->invoices as $invoice) {
                        $invoice->created_at = $reservation->date_out_time;
                        $invoice->save();
                    }
                }
            }
        }


        return $this->info('Every thing went successfully -__-');
    }
}
