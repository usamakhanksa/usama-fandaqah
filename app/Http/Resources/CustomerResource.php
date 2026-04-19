<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
            'id_number' => $this->id_number,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'nationality_string' => $this->nationality_string,
            'work' => $this->work,
            'work_phone' => $this->work_phone,
            'customer_type' => $this->customer_type,
            'customer_type_string' => $this->customer_type_string,
            'reservations_count' => $this->reservations_count,
            'id_type_string' => $this->id_type_string,
            'highlight' => $this->highlight,
        ];
    }


}
