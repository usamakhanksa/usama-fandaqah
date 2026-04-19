<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ServicesCategoryResource extends JsonResource
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
            'services' =>  ServiceResource::collection($this->services()->orderBy('order', 'asc')
            ->where('status', 1)->get()),
        ];
    }
}
