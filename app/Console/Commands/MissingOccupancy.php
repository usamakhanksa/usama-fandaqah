<?php

namespace App\Console\Commands;

use App\Team;
use App\Unit;
use App\Video;
use App\Country;
use App\Occupied;
use Carbon\Carbon;
use App\Reservation;
use App\Transaction;
use Faker\Factory as Faker;
use Illuminate\Http\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Jobs\CreateOccupiedForTeam;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use App\Jobs\SCTH\OccupancyUpdate as OccupancyUpdateJob;
use stdClass;

class MissingOccupancy extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missing:occupancy {team_id} {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get the missing occupancy for specific date and team';

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
        $team_id = $this->argument('team_id');
        $date = $this->argument('date');
        $team = Team::find($team_id);
        $data = $this->getData($team,$date);

        $occupied = new stdClass();
        $occupied->units_count = !is_null($data['units_count']) ? $data['units_count'] : 0;
        $occupied->available = $data['rooms_available'];
        $occupied->maintenance = $data['rooms_maintenance'];
        $occupied->cleaning = $data['rooms_cleaning'];
        $occupied->booked = $data['rooms_booked'];
        $occupied->occupied = $data['rooms_occupied'];

        $occupied->percentage = $data['units_count'] ?   number_format( ( ( ($data['rooms_occupied'] + $data['rooms_booked']) / $data['units_count'])  * 100 ) , 2)  : 0 ;
        $occupied->team_id = $data['team_id'];
        $occupied->created_at = $data['day'];

        dd($data,$occupied);
    }


    protected function getData($team,$date)
    {

        $yesterday = $date;
        $trashed_units = Unit::withTrashed()
            ->withoutGlobalScope('team_id')
            ->where('status', '!=', 0)
            ->whereTeamId($team->id)
            ->whereDate('deleted_at', $yesterday)
            ->whereEnabled(true)
            ->get();

        $units = Unit::where('status', '!=', 0)
            ->withoutGlobalScope('team_id')
            ->whereTeamId($team->id)
            ->whereDate('created_at', '<=', $yesterday)
            ->whereEnabled(true)
            ->get()
            ->merge($trashed_units);


        // this is the current implementation which is not accurate
//        $rooms_occupied = Reservation::withoutGlobalScope('team_id')
//            ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
//            ->whereDateBetween($yesterday)
//            ->whereNull('checked_out')
//            ->where('status', '!=', 'canceled')
//            ->count();
//
//        $rooms_booked = Reservation::withoutGlobalScope('team_id')
//            ->whereIn('unit_id', $units->pluck('id')->toArray())
//            ->whereDateBetween($yesterday)
//            ->whereNull('checked_in')
//            ->count();

        /**
        * @description :  Hot Fix To Accurate calculations of occupied , booked
         */
        $rooms_occupied = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateBetween($yesterday)
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->count();

        $rooms_booked = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateBetween($yesterday)
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();


//        $rooms_available = Unit::where('status', '!=', 0)
//            ->withoutGlobalScope('team_id')
//            ->whereTeamId($team->id)
//            ->whereDate('created_at', '<=', $yesterday->toDateString())
//            ->whereDoesntHave('reservations')
//            ->whereEnabled(true)
//            ->count();

//        $rooms_available = Unit::available($yesterday)
//            ->whereDate('created_at', '<=', $yesterday->toDateString())
//            ->whereTeamId($team->id)->count();

        $rooms_maintenance = Unit::underMaintenance()
            ->whereDate('created_at', '<=', $yesterday)
            ->whereTeamId($team->id)->count();

        $rooms_cleaning = Unit::underCleaning()
            ->whereDate('created_at', '<=', $yesterday)
            ->whereTeamId($team->id)->count();

        $rooms_available = count($units) - ($rooms_occupied + $rooms_booked + $rooms_maintenance + $rooms_cleaning );

        $units_count = count($units);

        return [
            'day' => $yesterday,
            'units_count' => $units_count,
            'rooms_available' => $rooms_available,
            'rooms_maintenance' => $rooms_maintenance,
            'rooms_cleaning' => $rooms_cleaning,
            'rooms_booked' => $rooms_booked,
            'rooms_occupied' => $rooms_occupied,
            'team_id' => $team->id,
        ];
    }
}
