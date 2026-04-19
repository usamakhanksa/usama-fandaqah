<?php

namespace App\Http\Resources\PublicApi\Hotels;

use App\Http\Resources\PublicApi\Units\UnitResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Vinkla\Hashids\Facades\Hashids;

class HotelResourceWithoutRelationships extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $hotelEncodedId = Hashids::encode($this->id);
        return [
            'id' => $hotelEncodedId ,
            'name' => $this->name ,
            'slug' => $this->slug ? $this->slug : '',
            'photo_url' => $this->photo_url ? $this->photo_url : '',
            'show_url' => url('/api/consumer/v1/hotels/') . '/' . $hotelEncodedId
        ];
    }
}
