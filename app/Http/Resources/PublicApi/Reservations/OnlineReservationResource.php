<?php

namespace App\Http\Resources\PublicApi\Reservations;

use App\Http\Resources\PublicApi\Customers\CustomerResource;
use App\Http\Resources\PublicApi\Units\UnitResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class OnlineReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $onlineReservationId = Hashids::encode($this->id);
        return [
            'id' => $onlineReservationId,
            'date_in' => $this->date_in->toDateString(),
            'date_out' => $this->date_out->toDateString(),
            'nights' => $this->nights,
            'unit' => new UnitResource($this->unit()->withOutGlobalScope('team_id')->first()),
            'customer' => new CustomerResource($this->customer()->withOutGlobalScope('team_id')->first()),
            'price' => $this->price,
            'status' => __(ucfirst($this->status))
        ];
    }
}
