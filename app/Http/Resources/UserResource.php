<?php

namespace App\Http\Resources;

use App\Http\Resources\HotelResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'email_is_verified' => (bool) ($this->email_verified_at),
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'photo_url' => $this->photo_url,
            'hotels' =>  HotelResource::collection($this->whenLoaded('teams')),
        ];
    }


}
