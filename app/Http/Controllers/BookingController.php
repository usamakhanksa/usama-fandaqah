<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingBlock;
use App\Models\BookingEvent;
use App\Http\Requests\SaveBookingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BookingController extends Controller {
    public function index(Request $request) {
        $bookings = Booking::query()
            ->when($request->search, fn($q, $s) => $q->search($s))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['search', 'status']),
            'metrics' => [
                'active_blocks' => BookingBlock::where('end_date', '>=', now()->toDateString())->count(),
                'upcoming_events' => BookingEvent::where('start_time', '>=', now()->toDateTimeString())->count(),
            ]
        ]);
    }

    public function store(SaveBookingRequest $request) {
        $data = $request->validated();
        $data['reference_code'] = 'BKG-' . strtoupper(Str::random(8));
        
        Booking::create($data);
        return redirect()->back()->with('success', 'Reservation created successfully.');
    }

    public function update(SaveBookingRequest $request, Booking $booking) {
        $booking->update($request->validated());
        return redirect()->back()->with('success', 'Reservation updated successfully.');
    }

    public function destroy(Booking $booking) {
        $booking->delete();
        return redirect()->back()->with('success', 'Reservation cancelled/deleted.');
    }
}
