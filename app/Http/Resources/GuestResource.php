<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'card_id' => $this->card_id,
            'booking' => $this->reservations_count,
            'type' => $this->type,
            'gender' => $this->gender,
            'company_profile_id' => $this->company_profile_id,
            'date_of_birth' => optional($this->date_of_birth)->toDateString(),
            'drop_down_civn' => $this->drop_down_civn,
            'address' => $this->address,
            'read_only_field' => $this->read_only_field,
        ];
    }
}
