<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnitCategoryMinifiedResource extends JsonResource
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
            'name' => $this->name,

        ];
    }
}
