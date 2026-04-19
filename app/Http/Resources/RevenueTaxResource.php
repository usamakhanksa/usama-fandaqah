<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueTaxResource extends JsonResource
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
            'name' => $this->customer()->withTrashed()->first()->name ?? null,
            'reservation_number' => $this->number,
            'checked_in' => $this->date_in_time,
            'checked_out' => $this->date_out_time,
            'duration' => $this->duration,
            'total' => $this->sub_total,
            'vat' => $this->vat_total,
            'ewa' => $this->ewa_total,
            'service' => 0,
        ];
    }

}
