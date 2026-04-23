<?php

namespace App\Http\Controllers;

use App\Models\BookingEvent;
use App\Http\Requests\SaveBookingEventRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingEventController extends Controller {
    public function index(Request $request) {
        $events = BookingEvent::query()
            ->when($request->search, function($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('property_reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Bookings/Events', [
            'events' => $events,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(SaveBookingEventRequest $request) {
        BookingEvent::create($request->validated());
        return redirect()->route('booking-events.index')->with('success', 'Event scheduled successfully.');
    }

    public function update(SaveBookingEventRequest $request, BookingEvent $bookingEvent) {
        $bookingEvent->update($request->validated());
        return redirect()->route('booking-events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(BookingEvent $bookingEvent) {
        $bookingEvent->delete();
        return redirect()->route('booking-events.index')->with('success', 'Event deleted successfully.');
    }
}
