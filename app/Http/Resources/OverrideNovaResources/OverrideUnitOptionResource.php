<?php

namespace App\Http\Resources\OverrideNovaResources;
use Illuminate\Http\Resources\Json\JsonResource;

class OverrideUnitOptionResource extends JsonResource
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
            'name' => $this->name,
        ];
    }

}
