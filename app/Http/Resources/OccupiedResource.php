<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class OccupiedResource extends JsonResource
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
            'day' => __($this->created_at->format('l')),
            'created_at' => $this->created_at->format('Y-m-d'),
            'units_count' => $this->units_count,
            'available' => $this->available,
            'occupied' => $this->occupied,
            'percentage' => $this->percentage . " %",
        ];
    }
}
