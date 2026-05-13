<?php

namespace App\Http\Controllers;

use App\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ArrivalsDeparturesController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        $arrivalsToday = Reservation::with(['customer', 'unit', 'creator'])
            ->where('date_in', $today)
            ->where('status', 'confirmed')
            ->get();

        $departuresToday = Reservation::with(['customer', 'unit', 'creator'])
            ->where('date_out', $today)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in')
            ->get();

        $inHouseGuests = Reservation::with(['customer', 'unit', 'creator'])
            ->where('date_in', '<=', $today)
            ->where('date_out', '>', $today)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in')
            ->whereNull('checked_out')
            ->get();

        return view('reservations.arrivals-departures', compact(
            'arrivalsToday',
            'departuresToday',
            'inHouseGuests'
        ));
    }

    public function getArrivalsDeparturesData(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($date);

        $arrivals = Reservation::with(['customer', 'unit', 'creator'])
            ->where('date_in', $date)
            ->where('status', 'confirmed')
            ->get();

        $departures = Reservation::with(['customer', 'unit', 'creator'])
            ->where('date_out', $date)
            ->where('status', 'confirmed')
            ->whereNotNull('checked_in')
            ->get();

        return response()->json([
            'arrivals' => $arrivals,
            'departures' => $departures,
        ]);
    }
}