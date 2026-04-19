<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'mobile_number' => $this->mobile_number,
            'responsible_person_name' => $this->responsible_person_name,
            'responsible_mobile_number' => $this->responsible_mobile_number,
            'id_type' => $this->id_type,
            'id_number' => $this->id_number,
            'email' => $this->email,
            'tax_number' => $this->tax_number,
            'country_id' => $this->country_id,
            'city_id' => $this->city_id,
            'city_name' => $this->city?->name,
            'address' => $this->address,
            'status' => $this->status,
            'bookings' => $this->guests_count,
            'type' => 'company',
            'media' => $this->media->map(fn ($m) => ['id' => $m->id, 'path' => $m->path]),
        ];
    }
}
