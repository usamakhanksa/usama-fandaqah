<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'old_unit_id' => $this->old_unit_id,
            'new_unit_id' => $this->new_unit_id,
            'old_date_in' => $this->old_date_in,
            'old_date_out' => $this->old_date_out,
            'new_date_in' => $this->new_date_in,
            'new_date_out' => $this->new_date_out,
            'old_price' => $this->old_price,
            'new_price' => $this->new_price,
            'reason' => $this->reason,
            'created_at' => $this->created_at,
        ];
    }
}
