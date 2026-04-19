<?php

namespace App\Http\Resources\Pos;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
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
        return [
            'id' => $this->id,
            'attachable_id' => $this->company_id && $this->attachable_id ? $this->attachable_id : $this->id ,
            'reservation_type' => $this->reservation_type,
            'unit' => new UnitResource($this->unit),
            'customer' => new CustomerResource($this->customer),
            'company' => $this->company_id ? new CompanyResource($this->company) : null
        ];
    }

}
