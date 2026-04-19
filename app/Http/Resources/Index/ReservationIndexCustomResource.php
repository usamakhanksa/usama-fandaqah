<?php

namespace App\Http\Resources\Index;

use App\Handlers\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ReservationIndexCustomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $date_in = new \DateTime($this->rdi);
        $date_out = new \DateTime($this->rdo);
        $nights = $date_out->diff($date_in)->days;

        return [
            'id' => $this->rid,
            'date_in' => $this->rdi,
            'date_out' => $this->rdo,
            'rent_type' => $this->rent_type,
            'checked_in' => $this->rchi,
            'status' => $this->rstatus,
            'customer_name' => $this->cname,
            'nights' => $nights,
            'balance' =>  $this->rb,
            'decimal_places' => $this->decimal_places
        ];
    }


}
