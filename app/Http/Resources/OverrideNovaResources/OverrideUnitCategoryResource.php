<?php

namespace App\Http\Resources\OverrideNovaResources;

use Illuminate\Http\Resources\Json\JsonResource;

class OverrideUnitCategoryResource extends JsonResource
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
            'name_array' => json_decode($this->getOriginal('name')),
            'status' => $this->status,
            'show_in_website' => $this->show_in_website,
            'units_count' => $this->units->count(),
            'main_image' => is_null($this->getFirstMediaUrl('main')) || empty($this->getFirstMediaUrl('main')) ? '/images/placeholder.jpg' : $this->getFirstMediaUrl('main'),
            'rooms_to_sell' => count($this->available_to_sync_units), // based on rooms related to the same category
            'prices' => $this->dailyPrices(),
            'virtual_rooms' => $this->virtual_room,

            'prices_as_days_names' => $this->dailyByDayNamePrices()
        ];
    }
}
