<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_name' => $this->name,
            'room_type' => $this->roomType?->name,
            'room_floor' => $this->roomFloor?->name ?? ('Fourth ('.$this->floor.')'),
            'room_type_id' => $this->room_type_id,
            'room_floor_id' => $this->room_floor_id,
            'number' => $this->number,
            'price_per_day' => $this->price_per_day,
            'status' => $this->status,
            'gender' => $this->gender,
            'thumbnail' => $this->thumbnail,
            'created_at' => $this->created_at,
        ];
    }
}
