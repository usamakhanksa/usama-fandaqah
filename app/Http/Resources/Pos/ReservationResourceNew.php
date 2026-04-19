<?php

namespace App\Http\Resources\Pos;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResourceNew extends JsonResource
{


    /**
     * UnitResource constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        static::$wrap = null;
        parent::__construct($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'rent_type' => $this->rent_type,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'nights' => $this->nights,
            'sub_total' => $this->sub_total,
            'total_price' => $this->total_price,
            'vat_total' => $this->vat_total,
            'unit_id' => $this->unit_id,
            'unit' => new UnitResource($this->unit),
            'customer' => new CustomerResource($this->customer)
        ];
    }

}
