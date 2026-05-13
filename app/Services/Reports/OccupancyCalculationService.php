<?php

namespace App\Services\Reports;

use App\Models\Room;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OccupancyCalculationService extends ReportService
{
    /**
     * Get occupancy statistics for a specific date
     */
    public function getDailyStats(Carbon $date)
    {
        $totalRooms = Room::where('team_id', $this->teamId)->count();
        
        $occupiedRooms = Reservation::where('team_id', $this->teamId)
            ->where('status', 'checked-in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->count();

        // Simple OOO logic - this might need adjustment based on how OOO is stored
        $oooRooms = DB::table('unit_maintenances')
            ->where('team_id', $this->teamId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->count();

        $availableRooms = $totalRooms - $occupiedRooms - $oooRooms;
        $occupancyPercentage = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

        return [
            'total_rooms' => $totalRooms,
            'occupied' => $occupiedRooms,
            'ooo' => $oooRooms,
            'available' => $availableRooms,
            'occupancy_percentage' => round($occupancyPercentage, 2),
            'date' => $date->toDateString(),
        ];
    }

    /**
     * Get occupancy statistics for a date range
     */
    public function getRangeStats(Carbon $startDate, Carbon $endDate)
    {
        $stats = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $stats[] = $this->getDailyStats($current->copy());
            $current->addDay();
        }

        return $stats;
    }

    /**
     * Get occupancy by room type
     */
    public function getOccupancyByRoomType(Carbon $date)
    {
        return DB::table('rooms')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->leftJoin('reservations', function($join) use ($date) {
                $join->on('rooms.id', '=', 'reservations.room_id')
                    ->where('reservations.status', '=', 'checked-in')
                    ->whereDate('reservations.check_in', '<=', $date)
                    ->whereDate('reservations.check_out', '>', $date);
            })
            ->where('rooms.team_id', $this->teamId)
            ->select(
                'room_types.name',
                DB::raw('count(rooms.id) as total'),
                DB::raw('count(reservations.id) as occupied')
            )
            ->groupBy('room_types.id', 'room_types.name')
            ->get()
            ->map(function($item) {
                $item->occupancy_percentage = $item->total > 0 ? round(($item->occupied / $item->total) * 100, 2) : 0;
                return $item;
            });
    }

    /**
     * Get occupancy by floor
     */
    public function getOccupancyByFloor(Carbon $date)
    {
        return DB::table('rooms')
            ->join('room_floors', 'rooms.room_floor_id', '=', 'room_floors.id')
            ->leftJoin('reservations', function($join) use ($date) {
                $join->on('rooms.id', '=', 'reservations.room_id')
                    ->where('reservations.status', '=', 'checked-in')
                    ->whereDate('reservations.check_in', '<=', $date)
                    ->whereDate('reservations.check_out', '>', $date);
            })
            ->where('rooms.team_id', $this->teamId)
            ->select(
                'room_floors.name',
                DB::raw('count(rooms.id) as total'),
                DB::raw('count(reservations.id) as occupied')
            )
            ->groupBy('room_floors.id', 'room_floors.name')
            ->get()
            ->map(function($item) {
                $item->occupancy_percentage = $item->total > 0 ? round(($item->occupied / $item->total) * 100, 2) : 0;
                return $item;
            });
    }
}
