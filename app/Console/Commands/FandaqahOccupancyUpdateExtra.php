<?php

namespace App\Console\Commands;
use App\Team;
use App\Unit;
use App\Occupied;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Console\Command;
use App\Jobs\CreateOccupiedForTeam;

class FandaqahOccupancyUpdateExtra extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:occupancy-update-extra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fandaqah occupancy update extra check to update tenants that didnt update';

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
        $team_ids = Team::whereNull('deleted_at')->get()->pluck('id');
        $targetDate = $this->ask('whats is the missing date?');
        $targetDate = Carbon::parse($targetDate)->format('Y-m-d');
        foreach ($team_ids as $team_id) {
            $checkOccupied = Occupied::where('team_id' , $team_ids)->where('created_at' , $targetDate)->first();
            if(!$checkOccupied){
                $data = $this->getData($team_id,$targetDate);
                $occupied = new Occupied;
                $occupied->units_count = !is_null($data['units_count']) ? $data['units_count'] : 0;
                $occupied->available = $data['rooms_available'];
                $occupied->maintenance = $data['rooms_maintenance'];
                $occupied->cleaning = $data['rooms_cleaning'];
                $occupied->booked = $data['rooms_booked'];
                $occupied->occupied = $data['rooms_occupied'];
                $occupied->percentage = $data['units_count'] ?   number_format(((($data['rooms_occupied'] + $data['rooms_booked']) / $data['units_count'])  * 100), 2)  : 0;
                $occupied->team_id = $data['team_id'];
                $occupied->created_at = $data['day'];
                $occupied->save();
            }
        }
    }


    protected function getData($team_id , $date)
    {
        $date = Carbon::parse($date);
        $trashed_units = Unit::withTrashed()
            ->withoutGlobalScope('team_id')
            ->where('status', '!=', 0)
            ->whereTeamId($team_id)
            ->whereDate('deleted_at', $date)
            ->whereEnabled(true)
            ->get();

        $units = Unit::where('status', '!=', 0)
            ->withoutGlobalScope('team_id')
            ->whereTeamId($team_id)
            ->whereDate('created_at', '<=', $date->toDateString())
            ->whereEnabled(true)
            ->get()
            ->merge($trashed_units);

        $rooms_occupied = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
            ->whereDateBetween($date)
            ->where('team_id', $team_id)
            ->where('status', '!=', 'canceled')
            // ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->count();

        $rooms_booked = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
            ->whereDateBetween($date)
            ->where('team_id', $team_id)
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();


        $rooms_maintenance = Unit::underMaintenance()
            ->whereDate('created_at', '<=', $date->toDateString())
            ->whereTeamId($team_id)->count();

        $rooms_cleaning = Unit::underCleaning()
            ->whereDate('created_at', '<=', $date->toDateString())
            ->whereTeamId($team_id)->count();

        $rooms_available = count($units) - ($rooms_occupied + $rooms_booked + $rooms_maintenance + $rooms_cleaning);
        $units_count = count($units);
        return [
            'day' => $date,
            'units_count' => $units_count,
            'rooms_available' => $rooms_available <= 0 ? 0 : $rooms_available,
            'rooms_maintenance' => $rooms_maintenance,
            'rooms_cleaning' => $rooms_cleaning,
            'rooms_booked' => $rooms_booked,
            'rooms_occupied' => $rooms_occupied,
            'team_id' => $team_id,
        ];
    }
}
