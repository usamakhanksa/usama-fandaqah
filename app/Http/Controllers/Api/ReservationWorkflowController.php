<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FinancialRecord;
use App\Models\Guest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\Reservation;
use App\Models\ReservationDraft;
use App\Models\ReservationNote;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationWorkflowController extends Controller
{
    public function schedule(Request $request)
    {
        $start = $request->date('start') ?: now()->startOfWeek();
        $end = $request->date('end') ?: now()->endOfWeek();

        $rooms = Room::query()->with(['roomType'])->limit(20)->get();
        $events = Reservation::query()
            ->with(['guest', 'room'])
            ->whereDate('check_in', '<=', $end)
            ->whereDate('check_out', '>=', $start)
            ->limit(120)
            ->get()
            ->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'room_id' => $reservation->room_id,
                    'guest' => $reservation->guest?->name,
                    'avatar' => $reservation->guest?->avatar,
                    'source' => 'Booking.com',
                    'price' => (float) optional($reservation->room)->price_per_day * max(1, $reservation->check_in->diffInDays($reservation->check_out)),
                    'status' => $reservation->reservation_status_id === 1 ? 'pending' : 'paid',
                    'start' => $reservation->check_in->toDateString(),
                    'end' => $reservation->check_out->toDateString(),
                ];
            });

        return response()->json([
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'rooms' => $rooms,
            'events' => $events,
        ]);
    }

    public function saveDraft(Request $request)
    {
        $data = $request->validate([
            'reference' => ['nullable', 'string'],
            'current_step' => ['required', 'integer', 'min:1', 'max:3'],
            'details_payload' => ['nullable', 'array'],
            'visitor_payload' => ['nullable', 'array'],
            'payment_payload' => ['nullable', 'array'],
        ]);

        $reference = $data['reference'] ?? ('DRF'.Str::upper(Str::random(8)));
        $draft = ReservationDraft::query()->updateOrCreate(
            ['reference' => $reference],
            [
                'user_id' => optional($request->user())->id,
                'current_step' => $data['current_step'],
                'details_payload' => $data['details_payload'] ?? null,
                'visitor_payload' => $data['visitor_payload'] ?? null,
                'payment_payload' => $data['payment_payload'] ?? null,
            ]
        );

        return response()->json($draft);
    }

    public function showDraft(string $reference)
    {
        return response()->json(ReservationDraft::query()->where('reference', $reference)->firstOrFail());
    }

    public function applyPromo(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $promo = PromoCode::query()->where('code', strtoupper($data['code']))->where('is_active', true)->first();
        if (!$promo || ($promo->expires_at && $promo->expires_at->isPast())) {
            return response()->json(['message' => 'Invalid promo code'], 422);
        }

        $discount = $promo->type === 'fixed'
            ? min($promo->value, $data['subtotal'])
            : ($data['subtotal'] * ($promo->value / 100));

        if ($promo->max_discount) {
            $discount = min($discount, $promo->max_discount);
        }

        return response()->json([
            'code' => $promo->code,
            'discount' => round($discount, 2),
            'total' => round($data['subtotal'] - $discount, 2),
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'reference' => ['nullable', 'string'],
            'details' => ['required', 'array'],
            'visitor' => ['required', 'array'],
            'payment' => ['required', 'array'],
        ]);

        $booking = DB::transaction(function () use ($data) {
            $guest = Guest::query()->firstOrCreate(
                ['email' => $data['visitor']['email'] ?? null, 'name' => $data['visitor']['visitor_name']],
                [
                    'phone' => $data['visitor']['phone'] ?? null,
                    'gender' => $data['visitor']['gender'] ?? 'male',
                    'type' => $data['visitor']['visitor_type'] ?? 'normal',
                    'avatar' => '/assets/avatars/guest1.svg',
                ]
            );

            $room = Room::query()->findOrFail($data['details']['room_id']);
            $reservation = Reservation::query()->create([
                'code' => 'RSV'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'unit_id' => $data['details']['unit_id'] ?? null,
                'reservation_status_id' => 2,
                'date_in' => $data['details']['check_in'],
                'date_out' => $data['details']['check_out'],
                'stay_type' => 'checkin',
            ]);

            $nights = max(1, \Carbon\Carbon::parse($data['details']['check_in'])->diffInDays(\Carbon\Carbon::parse($data['details']['check_out'])));
            $subtotal = (float) $room->price_per_day * $nights;
            $discount = (float) ($data['payment']['discount'] ?? 0);
            $total = max(0, $subtotal - $discount + 25.5 + 35);

            $booking = Booking::query()->create([
                'reservation_id' => $reservation->id,
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'check_in' => $reservation->check_in,
                'check_out' => $reservation->check_out,
                'total_amount' => $total,
            ]);

            Payment::query()->create([
                'booking_id' => $booking->id,
                'amount' => $total,
                'method' => $data['payment']['payment_method'] ?? 'cash',
            ]);

            $invoice = Invoice::query()->create([
                'booking_id' => $booking->id,
                'number' => 'INV'.str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
                'amount' => $total,
                'status' => 'paid',
            ]);

            FinancialRecord::query()->create(['booking_id' => $booking->id, 'label' => 'Room Charge', 'amount' => $subtotal, 'type' => 'debit']);
            FinancialRecord::query()->create(['booking_id' => $booking->id, 'label' => 'Discount', 'amount' => $discount, 'type' => 'credit']);
            FinancialRecord::query()->create(['booking_id' => $booking->id, 'label' => 'Invoice '.$invoice->number, 'amount' => $total, 'type' => 'debit']);

            if (!empty($data['reference'])) {
                ReservationDraft::query()->where('reference', $data['reference'])->delete();
            }

            return $booking;
        });

        return response()->json($booking, 201);
    }

    public function successData(Booking $booking)
    {
        $booking->load(['reservation', 'room']);

        return response()->json([
            'id' => $booking->id,
            'title' => 'Room Has Been Successfully Booked',
            'subtitle' => 'Be Ready To Check IN',
            'booking' => $booking,
            'receipt_url' => url('/api/reservations/receipt/'.$booking->id),
        ]);
    }

    public function bookingDetails(Booking $booking)
    {
        $booking->load(['reservation.guest', 'room']);

        return response()->json([
            'summary' => $booking,
            'visitor' => [
                ['label' => 'City', 'value' => 'Ad-Dilam'],
                ['label' => 'Region', 'value' => 'Medina Region'],
                ['label' => 'Nationality', 'value' => 'Saudi Arabia'],
                ['label' => 'Phone', 'value' => $booking->reservation?->guest?->phone],
                ['label' => 'Email', 'value' => $booking->reservation?->guest?->email],
            ],
            'financial' => FinancialRecord::query()->where('booking_id', $booking->id)->get(),
            'notes' => ReservationNote::query()->where('booking_id', $booking->id)->latest()->get(),
        ]);
    }



    public function receipt(Booking $booking)
    {
        $booking->load(['reservation.guest', 'room']);

        return response()->json([
            'invoice' => 'INV'.str_pad((string) $booking->id, 6, '0', STR_PAD_LEFT),
            'guest' => $booking->reservation?->guest?->name,
            'room' => $booking->room?->number,
            'check_in' => optional($booking->check_in)->toDateString(),
            'check_out' => optional($booking->check_out)->toDateString(),
            'total' => $booking->total_amount,
        ]);
    }

    public function addNote(Request $request, Booking $booking)
    {
        $data = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        $note = ReservationNote::query()->create([
            'booking_id' => $booking->id,
            'user_id' => optional($request->user())->id,
            'note' => $data['note'],
        ]);

        return response()->json($note, 201);
    }
}
