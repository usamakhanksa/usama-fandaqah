<?php

namespace App\Http\Resources\OverrideNovaResources;

use Illuminate\Http\Resources\Json\JsonResource;

class OverrideUnitResource extends JsonResource
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
            'team_id' => $this->team_id,
            'name' => $this->name,
            'unit_number' => $this->unit_number,
            'unit_category_id' => $this->unit_category ? $this->unit_category->id : null,
            'unit_category_name' => $this->unit_category ? $this->unit_category->name : null,
            'unit_category_type' => $this->unit_category ? $this->unit_category->type_id : null,
            'status' => $this->status,
            'enabled' => $this->enabled,
            'prices' => $this->prices(),
            'available_to_sync' => $this->available_to_sync,
            'reservations_count' => $this->getReservationsCount(),
            'main_image' => !$this->unit_category || is_null($this->unit_category->getFirstMediaUrl('main')) || empty($this->unit_category->getFirstMediaUrl('main')) ? '/images/placeholder.jpg' : $this->unit_category->getFirstMediaUrl('main'),
        ];
    }
}
