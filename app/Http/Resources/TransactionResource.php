<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'reservation_number' => $this->reservation->number,
            'unit_number' => $this->reservation->unit->unit_number ?? '-',
            'recived_from' => $this->meta['from'] ?? __('Nothing'),
            'amount' => abs($this->amount  / 100 ) .' '. __('SAR') ,
            'date_receipt' => $this->meta['date'] ?? '--',
            'payment_method' => $this->payment_method,
            'statement' => $this->meta['statement'] ?? null,
        ];
    }


}
