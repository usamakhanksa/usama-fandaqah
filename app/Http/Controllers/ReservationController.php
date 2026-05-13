<?php

namespace App\Http\Controllers;

use App\Reservation;
use App\GroupReservation;
use App\Customer;
use App\Unit;
use App\Company;
use App\ReservationContract;
use App\ReservationTransfer;
use App\ReservationExtension;
use App\Rating;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\ReservationCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of reservations with advanced filters and search
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['customer', 'unit', 'creator', 'source']);

        // Apply filters based on request parameters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reservation_type')) {
            $query->where('reservation_category_type', $request->reservation_type);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            $query->where(function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('date_in', [$dateFrom, $dateTo])
                  ->orWhereBetween('date_out', [$dateFrom, $dateTo])
                  ->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                      $subQuery->where('date_in', '<=', $dateFrom)
                               ->where('date_out', '>=', $dateTo);
                  });
            });
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('number', 'LIKE', "%{$searchTerm}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($searchTerm) {
                      $customerQuery->where('name', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('id_number', 'LIKE', "%{$searchTerm}%")
                                    ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                  })
                  ->orWhereHas('unit', function ($unitQuery) use ($searchTerm) {
                      $unitQuery->where('unit_number', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        $reservations = $query->paginate($request->get('per_page', 15));

        return new ReservationCollection($reservations);
    }

    /**
     * Show the form for creating a new reservation
     */
    public function create()
    {
        $customers = Customer::all();
        $units = Unit::available()->get();
        $companies = Company::all();
        
        return response()->json([
            'customers' => $customers,
            'units' => $units,
            'companies' => $companies,
            'sources' => \App\Source::all(),
            'reservation_types' => ['Normal', 'Complimentary', 'HouseUse', 'DayUse']
        ]);
    }

    /**
     * Store a newly created reservation in storage
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'unit_id' => 'required|exists:units,id',
            'date_in' => 'required|date|before:date_out',
            'date_out' => 'required|date|after:date_in',
            'status' => 'required|in:confirmed,pending,canceled',
            'reservation_category_type' => 'required|in:Normal,Complimentary,HouseUse,DayUse',
            'total_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reservation = new Reservation();
        $reservation->fill($request->except(['customer_id', 'unit_id', 'company_id']));
        $reservation->customer_id = $request->customer_id;
        $reservation->unit_id = $request->unit_id;
        $reservation->company_id = $request->company_id;
        $reservation->created_by = auth()->id();
        $reservation->number = $this->generateReservationNumber();
        $reservation->save();

        // Generate digital contract
        $this->generateDigitalContract($reservation);

        return new ReservationResource($reservation);
    }

    /**
     * Display the specified reservation
     */
    public function show(Reservation $reservation)
    {
        $reservation->load([
            'customer', 
            'unit', 
            'creator', 
            'source', 
            'invoices', 
            'transactions', 
            'promissory', 
            'reservationTransfers',
            'signedContracts',
            'ratings'
        ]);

        return new ReservationResource($reservation);
    }

    /**
     * Show the form for editing the specified reservation
     */
    public function edit(Reservation $reservation)
    {
        $customers = Customer::all();
        $units = Unit::available()->get();
        $companies = Company::all();
        
        return response()->json([
            'reservation' => new ReservationResource($reservation),
            'customers' => $customers,
            'units' => $units,
            'companies' => $companies,
            'sources' => \App\Source::all(),
            'reservation_types' => ['Normal', 'Complimentary', 'HouseUse', 'DayUse']
        ]);
    }

    /**
     * Update the specified reservation in storage
     */
    public function update(Request $request, Reservation $reservation)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'unit_id' => 'sometimes|required|exists:units,id',
            'date_in' => 'sometimes|required|date|before:date_out',
            'date_out' => 'sometimes|required|date|after:date_in',
            'status' => 'sometimes|required|in:confirmed,pending,canceled',
            'reservation_category_type' => 'sometimes|required|in:Normal,Complimentary,HouseUse,DayUse',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reservation->update($request->all());

        return new ReservationResource($reservation);
    }

    /**
     * Remove the specified reservation from storage
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->status = 'canceled';
        $reservation->cancellation_reason = request()->cancellation_reason ?? 'Deleted by admin';
        $reservation->save();

        return response()->json(['message' => 'Reservation canceled successfully']);
    }

    /**
     * Perform bulk actions on selected reservations
     */
    public function bulkActions(Request $request)
    {
        $action = $request->action;
        $reservationIds = $request->ids;

        $reservations = Reservation::whereIn('id', $reservationIds)->get();

        foreach ($reservations as $reservation) {
            switch ($action) {
                case 'bulk_check_in':
                    $reservation->checked_in = Carbon::now();
                    $reservation->status = 'confirmed';
                    break;
                case 'bulk_cancel':
                    $reservation->status = 'canceled';
                    $reservation->cancellation_reason = 'Bulk cancellation';
                    break;
                case 'bulk_export':
                    // Handle export logic
                    break;
                case 'bulk_print_invoices':
                    // Handle print invoices logic
                    break;
            }
            
            $reservation->save();
        }

        return response()->json(['message' => 'Bulk action completed successfully']);
    }

    /**
     * Process reservation check-in
     */
    public function checkIn(Reservation $reservation)
    {
        $reservation->checked_in = Carbon::now();
        $reservation->status = 'confirmed';
        $reservation->save();

        return new ReservationResource($reservation);
    }

    /**
     * Process reservation check-out
     */
    public function checkOut(Reservation $reservation)
    {
        $reservation->checked_out = Carbon::now();
        $reservation->save();

        return new ReservationResource($reservation);
    }

    /**
     * Extend reservation
     */
    public function extend(Request $request, Reservation $reservation)
    {
        $validator = Validator::make($request->all(), [
            'new_date_out' => 'required|date|after:date_out',
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $extension = new ReservationExtension();
        $extension->reservation_id = $reservation->id;
        $extension->original_date_out = $reservation->date_out;
        $extension->extended_date_out = $request->new_date_out;
        $extension->reason = $request->reason;
        $extension->created_by = auth()->id();
        $extension->save();

        // Update reservation date_out
        $reservation->date_out = $request->new_date_out;
        $reservation->extension_reason = $request->reason;
        $reservation->save();

        return response()->json(['message' => 'Reservation extended successfully', 'extension' => $extension]);
    }

    /**
     * Mark reservation as no-show
     */
    public function markNoShow(Reservation $reservation)
    {
        $reservation->noshow_flag = true;
        $reservation->save();

        return new ReservationResource($reservation);
    }

    /**
     * Transfer reservation to another unit
     */
    public function transfer(Request $request, Reservation $reservation)
    {
        $validator = Validator::make($request->all(), [
            'new_unit_id' => 'required|exists:units,id',
            'new_date_in' => 'required|date',
            'new_date_out' => 'required|date|after:new_date_in',
            'reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors], 422);
        }

        $transfer = new ReservationTransfer();
        $transfer->reservation_id = $reservation->id;
        $transfer->old_unit_id = $reservation->unit_id;
        $transfer->new_unit_id = $request->new_unit_id;
        $transfer->old_date_in = $reservation->date_in;
        $transfer->old_date_out = $reservation->date_out;
        $transfer->new_date_in = $request->new_date_in;
        $transfer->new_date_out = $request->new_date_out;
        $transfer->old_price = $reservation->total_price;
        $transfer->new_price = $request->new_price ?? $reservation->total_price;
        $transfer->reason = $request->reason;
        $transfer->created_by = auth()->id();
        $transfer->save();

        // Update reservation
        $reservation->unit_id = $request->new_unit_id;
        $reservation->date_in = $request->new_date_in;
        $reservation->date_out = $request->new_date_out;
        $reservation->total_price = $transfer->new_price;
        $reservation->save();

        return response()->json(['message' => 'Reservation transferred successfully', 'transfer' => $transfer]);
    }

    /**
     * Generate a unique reservation number
     */
    private function generateReservationNumber()
    {
        $prefix = date('Y') . substr(strtoupper(md5(uniqid(rand(), true))), 0, 3);
        $number = $prefix . rand(1000, 9999);
        
        while (Reservation::where('number', $number)->exists()) {
            $number = $prefix . rand(1000, 9999);
        }
        
        return $number;
    }

    /**
     * Generate digital contract for reservation
     */
    private function generateDigitalContract($reservation)
    {
        $contract = new ReservationContract();
        $contract->reservation_id = $reservation->id;
        $contract->status = 'pending';
        $contract->version = '1.0';
        $contract->save();

        return $contract;
    }
}