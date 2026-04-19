<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationTransferResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reservation_number' => $this->reservation->number,
            'old_unit_number' => $this->old_unit()->withTrashed()->first()->unit_number,
            'old_date_in' => $this->old_date_in,
            'old_date_out' => $this->old_date_out,
            'old_price' => $this->old_price,
            'new_unit_number' => $this->new_unit()->withTrashed()->first()->unit_number,
            'new_date_in' => $this->new_date_in,
            'new_date_out' => $this->new_date_out,
            'new_price' => $this->new_price,
            'reason' => $this->reason,
            'creator' => new UserResource($this->creator),

        ];
    }
}
