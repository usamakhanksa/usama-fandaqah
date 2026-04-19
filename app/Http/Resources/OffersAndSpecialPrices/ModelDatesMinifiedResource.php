<?php

namespace App\Http\Resources\OffersAndSpecialPrices;
use Illuminate\Http\Resources\Json\JsonResource;

class ModelDatesMinifiedResource extends JsonResource
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
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
        ];
    }

}
