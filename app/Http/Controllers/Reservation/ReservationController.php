<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Services\Reservation\ReservationService;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    protected ReservationService $service;

    public function __construct(ReservationService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $this->authorize('reservations.view');
        
        $reservations = $this->service->getReservations(
            $request->user()->currentTeam,
            $request->all()
        );
        
        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'filters' => $request->all(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('reservations.create');
        
        $data = $this->service->getCreateData($request->user()->currentTeam);
        
        return Inertia::render('Reservations/Create', $data);
    }

    public function store(StoreReservationRequest $request)
    {
        $this->authorize('reservations.create');
        
        $reservation = $this->service->createReservation(
            $request->user()->currentTeam,
            $request->validated()
        );
        
        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservation created successfully');
    }

    public function show(Reservation $reservation)
    {
        $this->authorize('reservations.view', $reservation);
        
        $data = $this->service->getReservationDetails($reservation);
        
        return Inertia::render('Reservations/Show', $data);
    }

    public function edit(Reservation $reservation)
    {
        $this->authorize('reservations.update', $reservation);
        
        $data = $this->service->getEditData($reservation);
        
        return Inertia::render('Reservations/Edit', $data);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation)
    {
        $this->authorize('reservations.update', $reservation);
        
        $this->service->updateReservation($reservation, $request->validated());
        
        return redirect()->route('reservations.show', $reservation)
            ->with('success', 'Reservation updated successfully');
    }

    public function destroy(Reservation $reservation)
    {
        $this->authorize('reservations.delete', $reservation);
        
        $this->service->deleteReservation($reservation);
        
        return redirect()->route('reservations.index')
            ->with('success', 'Reservation deleted successfully');
    }

    public function restore(Reservation $reservation)
    {
        $this->authorize('reservations.restore');
        
        $this->service->restoreReservation($reservation);
        
        return redirect()->route('reservations.index')
            ->with('success', 'Reservation restored successfully');
    }

    public function checkIn(Request $request, Reservation $reservation)
    {
        $this->authorize('reservations.checkin', $reservation);
        
        $this->service->checkIn($reservation, $request->all());
        
        return response()->json(['message' => 'Check-in completed successfully']);
    }

    public function checkOut(Request $request, Reservation $reservation)
    {
        $this->authorize('reservations.checkout', $reservation);
        
        $this->service->checkOut($reservation, $request->all());
        
        return response()->json(['message' => 'Check-out completed successfully']);
    }

    public function cancel(Reservation $reservation)
    {
        $this->authorize('reservations.cancel', $reservation);
        
        $this->service->cancelReservation($reservation);
        
        return response()->json(['message' => 'Reservation cancelled successfully']);
    }

    public function noShow(Reservation $reservation)
    {
        $this->authorize('reservations.no-show', $reservation);
        
        $this->service->markNoShow($reservation);
        
        return response()->json(['message' => 'Reservation marked as no-show']);
    }

    public function extend(Request $request, Reservation $reservation)
    {
        $this->authorize('reservations.extend', $reservation);
        
        $request->validate(['extension_days' => 'required|integer|min:1']);
        
        $this->service->extendReservation($reservation, $request->input('extension_days'));
        
        return response()->json(['message' => 'Reservation extended successfully']);
    }

    public function transfer(Request $request, Reservation $reservation)
    {
        $this->authorize('reservations.transfer', $reservation);
        
        $request->validate(['new_unit_id' => 'required|exists:units,id']);
        
        $this->service->transferReservation($reservation, $request->input('new_unit_id'));
        
        return response()->json(['message' => 'Reservation transferred successfully']);
    }

    public function arrivals(Request $request)
    {
        $this->authorize('reservations.view');
        
        $arrivals = $this->service->getArrivals(
            $request->user()->currentTeam,
            $request->all()
        );
        
        return Inertia::render('Reservations/Arrivals', ['arrivals' => $arrivals]);
    }

    public function departures(Request $request)
    {
        $this->authorize('reservations.view');
        
        $departures = $this->service->getDepartures(
            $request->user()->currentTeam,
            $request->all()
        );
        
        return Inertia::render('Reservations/Departures', ['departures' => $departures]);
    }

    public function inHouseGuests(Request $request)
    {
        $this->authorize('reservations.view');
        
        $guests = $this->service->getInHouseGuests(
            $request->user()->currentTeam,
            $request->all()
        );
        
        return Inertia::render('Reservations/InHouse', ['guests' => $guests]);
    }

    public function bulkActions(Request $request)
    {
        $this->authorize('reservations.bulk-actions');
        
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|string',
        ]);
        
        $this->service->bulkActions($request->input('ids'), $request->input('action'));
        
        return response()->json(['message' => 'Bulk action completed']);
    }

    public function export(Request $request)
    {
        $this->authorize('reservations.export');
        
        return $this->service->exportReservations($request->all());
    }

    public function import(Request $request)
    {
        $this->authorize('reservations.import');
        
        $request->validate(['file' => 'required|file|mimes:csv,xlsx']);
        
        $result = $this->service->importReservations($request->file('file'));
        
        return response()->json($result);
    }
}
