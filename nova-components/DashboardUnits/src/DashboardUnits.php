<?php

namespace SureLab\DashboardUnits;

use App\Reservation;
use App\Unit;
use Carbon\Carbon;
use Laravel\Nova\Card;
class DashboardUnits extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'DashboardUnits';
    }

    /**
     * @description : Occupancy
     * Inject Percentage For Pie Chart  Per Today
     * Injection in NovaServiceProvider
     * @return DashboardUnits | Occupied Percentage Per Today
     */
    public function occupiedPercentageToday(){


        $now = Carbon::now()->startOfDay();


//        $units_ids = Unit::whereEnabled(true)->where('status', '!=', 0)->pluck('id');
//
//
//        $rooms_occupied = Reservation::whereIn('unit_id', $units_ids->toArray())
//            ->whereDateBetween($now)
//            ->where('status', '!=', 'canceled')
//            ->whereNull('checked_out')
//            ->whereNotNull('checked_in')
//            ->count();
//
//        $rooms_booked = Reservation::withoutGlobalScope('team_id')
//            ->whereIn('unit_id', $units_ids->toArray())
//            ->whereDateBetween($now)
//            ->where('status', '!=', 'canceled')
//            ->whereNull('checked_out')
//            ->whereNull('checked_in')
//            ->count();


//        $occupied_percentage = (count($units_ids)) ? round(($rooms_occupied + $rooms_booked) / count($units_ids) *100, 2): 0;
        return $this->withMeta(['occupied_percentage_today' => 0]);

    }
}
