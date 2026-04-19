<?php

namespace App\Http\Resources\Charts;

use App\Unit;
use Carbon\Carbon;
use App\SpecialPrice;
use App\UnitCategory;
use Carbon\CarbonPeriod;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\MediaLibrary\Models\Media;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitCategoryResource extends JsonResource
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
            'name' => $this->name,
            'units' => UnitResource::collection($this->whenLoaded('allUnits')),
            'units_count' => count($this->whenLoaded('allUnits'))
        ];
    }

}
