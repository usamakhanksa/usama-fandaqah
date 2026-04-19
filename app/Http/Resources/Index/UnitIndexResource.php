<?php

namespace App\Http\Resources\Index;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class UnitIndexResource extends JsonResource
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
            'unit_number' => $this->unit_number,
//            'reservations_date' => $this->reservations_date,
            'accommodation_tax' => Cache::get(auth()->user()->current_team_id)['accommodation_tax'] ,
            'tax' => Cache::get(auth()->user()->current_team_id)['tax'] ,
            'tourism_tax' => Cache::get(auth()->user()->current_team_id)['tourism_tax'],
            'day_end' => Cache::get(auth()->user()->current_team_id)['day_end']
        ];
    }


}
