<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'entity_type' => $this->entity_type,
            'user_id' => $this->user_id,
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'person_incharge_name' => $this->person_incharge_name,
            'person_incharge_phone' => $this->person_incharge_phone,
            'address' => $this->address,
            'tax_number' => $this->tax_number,
            'postal_code' => $this->postal_code,
            'district' => $this->district,
            'building_number' => $this->building_number,
            'street_name' => $this->street_name,
            'country_id' => $this->country_id,
            'company_group_id' => $this->company_group_id,
            'payment_terms_days' => $this->payment_terms_days,
            'credit_limit' => $this->credit_limit,
            'currency' => $this->currency,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'country' => new CountryResource($this->whenLoaded('country')),
            'company_group' => new CompanyGroupResource($this->whenLoaded('companyGroup')),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),
        ];
    }
}