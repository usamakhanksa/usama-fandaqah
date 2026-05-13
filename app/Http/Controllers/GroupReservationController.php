<?php

namespace App\Http\Controllers;

use App\GroupReservation;
use App\Reservation;
use App\Customer;
use App\Unit;
use App\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupReservationController extends Controller
{
    public function index()
    {
        $groupReservations = GroupReservation::with(['company'])->paginate(15);
        return view('group-reservations.index', compact('groupReservations'));
    }

    public function create()
    {
        $customers = Customer::all();
        $units = Unit::available()->get();
        $companies = Company::all();
        
        return response()->json([
            'customers' => $customers,
            'units' => $units,
            'companies' => $companies,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $groupReservation = new GroupReservation();
        $groupReservation->name = $request->name;
        $groupReservation->company_id = $request->company_id;
        $groupReservation->data = $request->data;
        $groupReservation->save();

        // Create individual reservations
        foreach ($request->data['reservations'] as $reservationData) {
            $reservation = new Reservation();
            $reservation->fill($reservationData);
            $reservation->group_reservation_id = $groupReservation->id;
            $reservation->save();
        }

        return response()->json(['message' => 'Group reservation created successfully', 'groupReservation' => $groupReservation]);
    }

    public function show(GroupReservation $groupReservation)
    {
        $groupReservation->load(['company', 'reservations.customer', 'reservations.unit']);
        return response()->json($groupReservation);
    }

    public function edit(GroupReservation $groupReservation)
    {
        $groupReservation->load(['company', 'reservations.customer', 'reservations.unit']);
        $customers = Customer::all();
        $units = Unit::available()->get();
        $companies = Company::all();
        
        return response()->json([
            'groupReservation' => $groupReservation,
            'customers' => $customers,
            'units' => $units,
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, GroupReservation $groupReservation)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $groupReservation->name = $request->name;
        $groupReservation->company_id = $request->company_id;
        $groupReservation->data = $request->data;
        $groupReservation->save();

        // Update individual reservations
        foreach ($request->data['reservations'] as $reservationData) {
            if (isset($reservationData['id'])) {
                $reservation = Reservation::find($reservationData['id']);
                $reservation->fill($reservationData);
                $reservation->save();
            } else {
                $reservation = new Reservation();
                $reservation->fill($reservationData);
                $reservation->group_reservation_id = $groupReservation->id;
                $reservation->save();
            }
        }

        return response()->json(['message' => 'Group reservation updated successfully', 'groupReservation' => $groupReservation]);
    }

    public function destroy(GroupReservation $groupReservation)
    {
        $groupReservation->delete();
        return response()->json(['message' => 'Group reservation deleted successfully']);
    }
}