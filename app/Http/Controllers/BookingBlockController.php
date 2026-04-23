<?php

namespace App\Http\Controllers;

use App\Models\BookingBlock;
use App\Http\Requests\SaveBookingBlockRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingBlockController extends Controller {
    public function index(Request $request) {
        $blocks = BookingBlock::query()
            ->when($request->search, function($q, $search) {
                $q->where('property_reference', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Bookings/Blocks', [
            'blocks' => $blocks,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(SaveBookingBlockRequest $request) {
        BookingBlock::create($request->validated());
        return redirect()->route('booking-blocks.index')->with('success', 'Block created successfully.');
    }

    public function update(SaveBookingBlockRequest $request, BookingBlock $bookingBlock) {
        $bookingBlock->update($request->validated());
        return redirect()->route('booking-blocks.index')->with('success', 'Block updated successfully.');
    }

    public function destroy(BookingBlock $bookingBlock) {
        $bookingBlock->delete();
        return redirect()->route('booking-blocks.index')->with('success', 'Block removed successfully.');
    }
}
