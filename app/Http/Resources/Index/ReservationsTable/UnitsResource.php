<?php

namespace App\Http\Resources\Index\ReservationsTable;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class UnitsResource extends JsonResource
{

    private $dates ;
    public function __construct($resource,$dates)
    {
        parent::__construct($resource);
        $this->dates = $dates;
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
            'number' => $this->unit_number ,
            'sunday_day_price' => $this->sunday_day_price ,
            'monday_day_price' => $this->monday_day_price ,
            'tuesday_day_price' => $this->tuesday_day_price ,
            'wednesday_day_price' => $this->wednesday_day_price ,
            'thursday_day_price' => $this->thursday_day_price ,
            'friday_day_price' => $this->friday_day_price ,
            'saturday_day_price' => $this->saturday_day_price ,

        ];
    }


}
