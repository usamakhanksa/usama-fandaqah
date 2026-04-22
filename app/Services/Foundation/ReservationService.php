<?php

namespace App\Services\Foundation;

use App\Models\Foundation\Reservation;
use Carbon\Carbon;

class ReservationService
{
    public function create(array $data): Reservation
    {
        return Reservation::create($this->hydrateCalculated($data));
    }

    public function update(Reservation $reservation, array $data): Reservation
    {
        $reservation->update($this->hydrateCalculated($data));

        return $reservation->refresh();
    }

    private function hydrateCalculated(array $data): array
    {
        $checkIn = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);
        $nights = (int) $checkIn->diffInDays($checkOut);
        $subtotal = round($nights * (float) $data['night_rate_sar'], 2);
        $tax = (float) ($data['tax_sar'] ?? 0);
        $data['reservation_no'] = $data['reservation_no'] ?? 'RSV-'.now()->format('YmdHis').'-'.random_int(100, 999);
        $data['nights'] = max($nights, 1);
        $data['children'] = (int) ($data['children'] ?? 0);
        $data['subtotal_sar'] = $subtotal;
        $data['tax_sar'] = $tax;
        $data['total_sar'] = $subtotal + $tax;
        $data['confirmed_at'] = in_array($data['status'], ['confirmed', 'checked_in', 'checked_out'], true) ? now() : null;

        return $data;
    }
}
