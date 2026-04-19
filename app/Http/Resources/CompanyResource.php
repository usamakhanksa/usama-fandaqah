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
            'name' => $this->name, 
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'address' => $this->address,
            'person_incharge_name' => $this->person_incharge_name,
            'person_incharge_phone' => $this->person_incharge_phone,
            'tax_number' => $this->tax_number,
            'added_by' => $this->creator ? $this->creator->name : '-'
        ];
    }
}
