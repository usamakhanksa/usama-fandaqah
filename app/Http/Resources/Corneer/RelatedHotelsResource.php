<?php

namespace App\Http\Resources\Corneer;

use App\Http\Resources\ReservationResource;
use App\Team;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;
use Vinkla\Hashids\Facades\Hashids;

class RelatedHotelsResource extends JsonResource
{
    protected $date;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        // Ensure you call the parent constructor
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
            'city_id' => $this->city_id,
            'city_title' => $this->city->title,
            'show_in_booking_engine' => $this->websiteSetting->show_in_booking_engine
        ];
    }
}
