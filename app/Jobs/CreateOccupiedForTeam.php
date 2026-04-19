<?php

namespace App\Jobs;

use App\Occupied;
use App\Reservation;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOccupiedForTeam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 3;

    protected $team_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($team_id)
    {
        $this->team_id = $team_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->getData();
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

    protected function getData()
    {
        $yesterday = Carbon::yesterday();
        $trashed_units = Unit::withTrashed()
            ->withoutGlobalScope('team_id')
            ->where('status', '!=', 0)
            ->whereTeamId($this->team_id)
            ->whereDate('deleted_at', $yesterday)
            ->whereEnabled(true)
            ->get();

        $units = Unit::where('status', '!=', 0)
            ->withoutGlobalScope('team_id')
            ->whereTeamId($this->team_id)
            ->whereDate('created_at', '<=', $yesterday->toDateString())
            ->whereEnabled(true)
            ->get()
            ->merge($trashed_units);

        $rooms_occupied = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
            ->whereDateBetween($yesterday)
            ->where('team_id', $this->team_id)
            ->where('status', '!=', 'canceled')
            // ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->count();

        $rooms_booked = Reservation::withoutGlobalScope('team_id')
            ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
            ->whereDateBetween($yesterday)
            ->where('team_id', $this->team_id)
            ->where('status', '!=', 'canceled')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();


        $rooms_maintenance = Unit::underMaintenance()
            ->whereDate('created_at', '<=', $yesterday->toDateString())
            ->whereTeamId($this->team_id)->count();

        $rooms_cleaning = Unit::underCleaning()
            ->whereDate('created_at', '<=', $yesterday->toDateString())
            ->whereTeamId($this->team_id)->count();

        $rooms_available = count($units) - ($rooms_occupied + $rooms_booked + $rooms_maintenance + $rooms_cleaning);
        $units_count = count($units);
        return [
            'day' => $yesterday,
            'units_count' => $units_count,
            'rooms_available' => $rooms_available <= 0 ? 0 : $rooms_available,
            'rooms_maintenance' => $rooms_maintenance,
            'rooms_cleaning' => $rooms_cleaning,
            'rooms_booked' => $rooms_booked,
            'rooms_occupied' => $rooms_occupied,
            'team_id' => $this->team_id,
        ];
    }
}
