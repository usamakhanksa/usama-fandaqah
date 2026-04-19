<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
            'number' => $this->number,
            'team_id' => $this->team_id,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'unit_id' => $this->unit_id,
            'total_price' => $this->total_price,
            'sub_total' => $this->sub_total,
            'ewa_total' => $this->ewa_total,
            'vat_total' => $this->vat_total,
            'created_by' => $this->created_by,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'checked_in' => $this->checked_in,
            'checked_out' => $this->checked_out,

            'purpose_of_visit' => $this->purpose_of_visit,
            'checked_out' => $this->checked_out,
            'checked_out' => $this->checked_out,
            'checked_out' => $this->checked_out,
            'date_out' => new CustomerResource($this->customer),
        ];
    }


}
