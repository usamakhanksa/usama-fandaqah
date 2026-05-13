<?php

namespace App\Http\Controllers;

use App\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationCalendarController extends Controller
{
    public function index()
    {
        return view('reservations.calendar');
    }

    public function getCalendarEvents(Request $request)
    {
        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $reservations = Reservation::with(['customer', 'unit'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date_in', [$startDate, $endDate])
                      ->orWhereBetween('date_out', [$startDate, $endDate])
                      ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                          $subQuery->where('date_in', '<=', $startDate)
                                   ->where('date_out', '>=', $endDate);
                      });
            })
            ->get();

        $events = [];
        foreach ($reservations as $reservation) {
            $events[] = [
                'id' => $reservation->id,
                'title' => $reservation->customer->name . ' - ' . $reservation->unit->unit_number,
                'start' => $reservation->date_in,
                'end' => $reservation->date_out,
                'backgroundColor' => $this->getStatusColor($reservation->status),
                'borderColor' => $this->getStatusColor($reservation->status),
                'extendedProps' => [
                    'reservationNumber' => $reservation->number,
                    'customerId' => $reservation->customer_id,
                    'unitId' => $reservation->unit_id,
                    'status' => $reservation->status,
                    'categoryType' => $reservation->reservation_category_type,
                    'totalPrice' => $reservation->total_price,
                ]
            ];
        }

        return response()->json($events);
    }

    private function getStatusColor($status)
    {
        switch ($status) {
            case 'confirmed':
                return '#28a745'; // Green
            case 'pending':
                return '#ffc107'; // Yellow
            case 'canceled':
                return '#dc3545'; // Red
            default:
                return '#6c757d'; // Gray
        }
    }
}