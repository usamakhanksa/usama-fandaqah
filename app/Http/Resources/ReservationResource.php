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
            'unit_id' => $this->unit_id,
            'unit_number' => $this->unit()->withTrashed()->first()->unit_number ?? null,
            'number' => $this->number,
            'status' => $this->getStatus(),
            'checked_in' => $this->checked_in,
            'checked_out' => $this->checked_out,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'nights' => $this->nights,
            'over_out_nights' => $this->over_out_nights,
            'purpose_of_visit' => $this->purpose_of_visit,
            'scth_reference' => $this->scth_reference,
            'balance' => $this->balance,
            'customer' => new CustomerResource($this->customer),
        ];
    }


    protected function getStatus()
    {
        if (is_null($this->checked_in) && is_null($this->checked_out)) {
            $label = __('Pending');
            $color = 'blue';
        } else if (!is_null($this->checked_in) && is_null($this->checked_out)) {
            $label = __('Checked In');
            $color = 'green';
        } else {
            $label = __('Checked Out');
            $color = 'red';
        }
        return $label;
    }

}
