<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ReservationResource;

class GuestResource extends JsonResource
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
            'company_profile_id' => $this->company_profile_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'type' => $this->type,
            'gender' => $this->gender,
            'card_id' => $this->card_id,
            'date_of_birth' => $this->date_of_birth,
            'drop_down_civn' => $this->drop_down_civn,
            'address' => $this->address,
            'nationality' => $this->nationality,
            'id_type' => $this->id_type,
            'id_number' => $this->id_number,
            'shomoos_verified_at' => $this->shomoos_verified_at,
            'shomoos_reference' => $this->shomoos_reference,
            'shomoos_status' => $this->shomoos_status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
        ];
    }
}
