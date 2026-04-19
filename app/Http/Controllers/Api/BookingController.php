<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
class BookingController extends Controller {
  public function store(StoreBookingRequest $request){
    $booking=Booking::create($request->validated());
    return response()->json($booking,201);
  }
  public function update(StoreBookingRequest $request, Booking $booking){
    $booking->update($request->validated());
    return response()->json($booking);
  }
}
