<?php

namespace App\Http\Resources\Index;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;

class ReservationIndexPanelsResource extends JsonResource
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
                'date_in' => $this->date_in,
                'date_out' => $this->date_out,
                'nights' => $this->nights,
                'balance' => $this->balance / 100,
                'checked_in' => $this->checked_in ,
                'checked_out' => $this->checked_out ,
                'unit' => new UnitIndexPanelsResource($this->unit),
                'customer' => new CustomerIndexResource($this->customer)
        ];
    }


}
