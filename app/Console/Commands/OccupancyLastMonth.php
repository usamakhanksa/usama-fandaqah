<?php

namespace App\Console\Commands;

use App\Occupied;
use App\Reservation;
use App\Role;
use App\Team;
use App\Unit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OccupancyLastMonth extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'occupancy:lastmonth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update App';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $teams = Team::all();
        $bar = $this->output->createProgressBar(count($teams));
        $bar->start();
        foreach ($teams as $team) {
            foreach (CarbonPeriod::create('2019-10-01', '2019-11-02') as $yesterday) {
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
                    ->whereDate('created_at', '<=', $yesterday->toDateString())
                    ->whereEnabled(true)
                    ->get()
                    ->merge($trashed_units);

                $rooms_occupied = Reservation::withoutGlobalScope('team_id')
                    ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
                    ->whereDateBetween($yesterday)
                    ->whereNull('checked_out')
                    ->where('status', '!=', 'canceled')
                    ->count();

                $rooms_booked = Reservation::withoutGlobalScope('team_id')
                    ->whereIn('unit_id', $units->pluck('id')->toArray())
                    ->whereDateBetween($yesterday)
                    ->whereNull('checked_in')
                    ->count();

                $rooms_available = Unit::available($yesterday)
                    ->whereDate('created_at', '<=', $yesterday->toDateString())
                    ->whereTeamId($team->id)->count();                

                $rooms_maintenance = Unit::underMaintenance()
                    ->whereDate('created_at', '<=', $yesterday->toDateString())
                    ->whereTeamId($team->id)->count();

                $rooms_cleaning = Unit::underCleaning()
                    ->whereDate('created_at', '<=', $yesterday->toDateString())
                    ->whereTeamId($team->id)->count();

                $occupied = new Occupied;
                $occupied->units_count = count($units);
                $occupied->available = $rooms_available;
                $occupied->maintenance = $rooms_maintenance;
                $occupied->cleaning = $rooms_cleaning;
                $occupied->booked = $rooms_booked;
                $occupied->occupied = $rooms_occupied;
                $occupied->percentage = ($rooms_occupied) ? $rooms_occupied / count($units) * 100 : 0 ;
                $occupied->team_id = $team->id;
                $occupied->created_at = $yesterday;
                $occupied->save();
            }


            $bar->advance();
        }
        $bar->finish();
    }
}
