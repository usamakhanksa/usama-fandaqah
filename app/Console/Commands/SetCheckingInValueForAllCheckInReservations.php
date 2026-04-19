<?php

namespace App\Console\Commands;

use App\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SetCheckingInValueForAllCheckInReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:checking-in-flag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command will update checking in value for all checked in reservations';

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
        $reservations = Reservation::setEagerLoads([])
        ->where('status', 'confirmed')
        ->whereNotNull('checked_in')
        ->whereNull('checked_out')
        ->whereNull('deleted_at')
        ->where('date_in' , '>=' , Carbon::today()->format('Y-m-d'))
        ->get();
        if(count($reservations)){
            foreach ($reservations as $reservation) {
                $reservation->checking_in = 1;
                $reservation->save();
            }
        }
    }
}
