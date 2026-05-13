<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReservationResource extends JsonResource
{
    public function toArray($request)
    {
        // Support both legacy (date_in/date_out/customer) and new (check_in/check_out/guest) column names
        $checkIn  = $this->date_in  ?? $this->check_in;
        $checkOut = $this->date_out ?? $this->check_out;
        $guest    = $this->customer ?? $this->guest ?? null;
        $unit     = $this->unit ?? null;
        $source   = $this->source ?? null;

        return [
            'id'         => $this->id,
            'code'       => $this->number ?? $this->code,
            'status'     => $this->status,
            'check_in'   => $checkIn ? Carbon::parse($checkIn)->format('Y-m-d') : null,
            'check_out'  => $checkOut ? Carbon::parse($checkOut)->format('Y-m-d') : null,
            'checked_in' => $this->checked_in,
            'checked_out'=> $this->checked_out,
            'nights'     => ($checkIn && $checkOut) ? Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)) : 0,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'team_id'    => $this->team_id,
            'cancellation_reason' => $this->cancellation_reason,
            'special_request'     => $this->special_request ?? $this->notes,
            'is_online'  => $this->is_online ?? false,
            'guest' => $guest ? [
                'id'    => $guest->id,
                'name'  => $guest->name,
                'phone' => $guest->phone,
                'email' => $guest->email,
                'avatar'=> $guest->avatar ?? null,
            ] : null,
            'unit' => $unit ? [
                'id'          => $unit->id,
                'number'      => $unit->unit_number ?? $unit->number,
                'floor'       => $unit->floor,
                'status'      => $unit->status,
            ] : null,
            'source' => $source ? [
                'id'   => $source->id,
                'name' => is_array($source->name) ? ($source->name['en'] ?? reset($source->name)) : $source->name,
            ] : null,
            'booking' => $this->whenLoaded('booking', fn() => [
                'total_amount' => $this->booking->total_amount ?? 0,
            ]),
        ];
    }
}
