<?php

namespace App\Http\Resources\PublicApi\Units;

use App\UnitOption;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;
class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $unitIdEncoded = Hashids::encode($this->id);
        return [
            'id' => $unitIdEncoded ,
            'unit_number' => $this->unit_number,
            'unit_name' => $this->name,
            'hotel' => $this->team->name,
            'reservations_count' => $this->reservations_count
        ];
    }
}
