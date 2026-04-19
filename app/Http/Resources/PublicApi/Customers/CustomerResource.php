<?php

namespace App\Http\Resources\PublicApi\Customers;

use App\Http\Resources\PublicApi\Hotels\HotelResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        $customerEncodedId = Hashids::encode($this->id);
        return [
//            'id' => $customerEncodedId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone
        ];
    }
}
