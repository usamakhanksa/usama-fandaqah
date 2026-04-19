<?php

namespace App\Http\Resources\Corneer;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class OnlineReservationResource extends JsonResource
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
            'date_in' => Carbon::parse($this->date_in)->format('Y/m/d'),
            'date_out' => Carbon::parse($this->date_out)->format('Y/m/d'),
            'created_at' => Carbon::parse($this->created_at)->format('Y/m/d h:i a'),
            'number' => $this->number ?? null,
            'status' => $this->status,
            'nights' => $this->nights,
            'price' => $this->total_price,
            'unit_name' => $this->unit->unit_category->name,
            'unit_id' => $this->unit->id,
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_email' => $this->customer->email,
            'customer_address' => $this->customer->address,
            'notes' => '',
            'team_name' => $this->team['name']
        ];
    }


}
