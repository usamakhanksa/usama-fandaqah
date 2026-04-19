<?php

namespace App\Console\Commands;

use App\Team;
use App\Unit;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Console\Command;
use App\Events\ReservationRenewed;
use App\Jobs\SCTH\ScthDayClosingOccupancyUpdate;
use Illuminate\Support\Facades\DB;
use App\Jobs\UpdateReservationDateOut;
use App\Setting;

class AutoRenewReservationDateOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-renew:reservation-dateout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command is responsible for update reservation dateout time if auto renewal option is active in settings';

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


        /**  General Setup */
        $now = now('Asia/Riyadh');
        $todayDate = $now->format('Y-m-d');
        $nextDate = $now->addDay()->format('Y-m-d');
        $now =  date('H:i');
        // collection of day_ends settings who is the tenants that checkout time has come to light
        // $day_ends = DB::table('settings')
        //     ->where('key', '=', 'day_end')
        //     ->where('value', '=', (string)$now)
        //     ->get();

        // Using Settings Model To Eager Load Team Relation Needed For Chained Occupancy
        $day_ends = Setting::where('key', '=', 'day_end')
            ->with('team')
            ->where('value', '=', (string)$now)
            ->get();

            if($day_ends->isNotEmpty()){
                foreach ($day_ends as $obj) {
                    UpdateReservationDateOut::dispatch($obj->team_id,$todayDate,$nextDate,$now);
                }
            }
    }

}
