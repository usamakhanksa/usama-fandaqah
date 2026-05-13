<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'name' => $this->name,
            'number' => $this->number,
            'status' => $this->status,
            'room_id' => $this->room_id,
            'room_floor_id' => $this->room_floor_id,
            'unit_type_id' => $this->unit_type_id,
            'unit_status_id' => $this->unit_status_id,
            'capacity' => $this->capacity,
            'beds' => $this->beds,
            'baths' => $this->baths,
            'thumbnail' => $this->thumbnail,
            'is_demo' => $this->is_demo,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'room' => new RoomResource($this->whenLoaded('room')),
            'room_floor' => new RoomFloorResource($this->whenLoaded('roomFloor')),
            'unit_type' => new UnitTypeResource($this->whenLoaded('unitType')),
            'unit_status' => new UnitStatusResource($this->whenLoaded('unitStatus')),
        ];
    }
}