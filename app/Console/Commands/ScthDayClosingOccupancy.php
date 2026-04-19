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

class ScthDayClosingOccupancy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scth:day-closing-occupancy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this command is responsible for sending a day closing occupancy update to the ntmp (scth) according to hotel day end';

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
        $now =  date('H:i');

        // Using Settings Model To Eager Load Team Relation Needed For Chained Occupancy
        $day_ends = Setting::where('key', '=', 'day_end')
            ->with('team')
            ->where('value', '=', (string)$now)
            ->get();

            if($day_ends->isNotEmpty()){
                foreach ($day_ends as $obj) {
                    ScthDayClosingOccupancyUpdate::dispatch($obj->team);
                }
            }
    }

}
