<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reservation_id' => $this->reservation_id,
            'total_amount' => $this->total_amount,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
        ];
    }
}
