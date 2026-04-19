<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitCategoryForOffersAndSpecialPrices extends JsonResource
{

    public function __construct($resource)
    {
        static::$wrap = null;
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->attributesToArray()['name'],
        ];
    }
}
