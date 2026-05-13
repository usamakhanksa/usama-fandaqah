<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Unit;
use App\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class FrontDeskController extends Controller
{
    /**
     * Get data for the Tape Chart (Room Grid).
     */
    public function tapeChart(Request $request)
    {
        $startDate = Carbon::parse($request->string('start_date', now()->toDateString()));
        $endDate = $startDate->copy()->addDays(14);
        $period = CarbonPeriod::create($startDate, $endDate);

        $units = Unit::with(['unitCategory'])
            ->where('team_id', $request->user()->current_team_id)
            ->get();

        $reservations = Reservation::with(['customer'])
            ->where('team_id', $request->user()->current_team_id)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('date_in', [$startDate, $endDate])
                      ->orWhereBetween('date_out', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('date_in', '<=', $startDate)
                            ->where('date_out', '>=', $endDate);
                      });
            })
            ->where('status', '!=', 'canceled')
            ->get();

        return response()->json([
            'dates' => collect($period)->map(fn($date) => [
                'full' => $date->toDateString(),
                'day' => $date->format('d'),
                'label' => $date->format('D'),
            ]),
            'units' => $units->map(fn($unit) => [
                'id' => $unit->id,
                'number' => $unit->unit_number,
                'category' => $unit->unitCategory->name ?? 'N/A',
                'status' => $unit->status,
                'reservations' => $reservations->where('unit_id', $unit->id)->map(fn($res) => [
                    'id' => $res->id,
                    'customer' => $res->customer->name ?? 'N/A',
                    'date_in' => $res->date_in,
                    'date_out' => $res->date_out,
                    'status' => $res->status,
                    'color' => $this->getStatusColor($res->status),
                ])->values()
            ])
        ]);
    }

    /**
     * Update room status.
     */
    public function updateRoomStatus(Request $request, Unit $unit)
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $unit->update(['status' => $request->integer('status')]);

        return response()->json(['message' => __('Room status updated successfully')]);
    }

    /**
     * Switch room for a reservation.
     */
    public function switchRoom(Request $request, Reservation $reservation)
    {
        $request->validate([
            'new_unit_id' => 'required|exists:units,id'
        ]);

        $oldUnitId = $reservation->unit_id;
        $reservation->update(['unit_id' => $request->integer('new_unit_id')]);

        // Optional: Update room statuses if reservation is checked in
        if ($reservation->checked_in && !$reservation->checked_out) {
            Unit::where('id', $oldUnitId)->update(['status' => 2]); // Cleaning
            Unit::where('id', $request->integer('new_unit_id'))->update(['status' => 5]); // Occupied
        }

        return response()->json(['message' => __('Room switched successfully')]);
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'confirmed' => '#3b82f6', // blue
            'in-house' => '#10b981', // green (if status exists)
            'checked-out' => '#6b7280', // gray
            'hold' => '#f59e0b', // amber
            default => '#3b82f6',
        };
    }
}
