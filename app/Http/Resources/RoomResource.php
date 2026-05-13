<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'name' => $this->name,
            'number' => $this->number,
            'type' => $this->type,
            'status' => $this->status,
            'floor' => $this->roomFloor?->name ?? 'Fourth (' . $this->floor . ')',
            'max_occupancy' => $this->max_occupancy,
            'amenities' => $this->amenities,
            'price_per_day' => $this->price_per_day,
            'gender' => $this->gender,
            'thumbnail' => $this->thumbnail,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
        ];
    }
}
