<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;
use Vinkla\Hashids\Facades\Hashids;

class UnitResource extends JsonResource
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
        $this->resource = $resource;
        $this->date = Carbon::parse(request()->date);
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
            'hash'  =>  Hashids::encode($this->id),
            'name' => $this->name,
            'team_id' => $this->team_id,
            'unit_number' => $this->unit_number,
            'status' => $this->status,
            'main_image'    =>  is_null($this->getFirstMediaUrl('main')) || empty($this->getFirstMediaUrl('main')) ? asset('images/placeholder.jpg') : $this->getFirstMediaUrl('main'),
            'description' =>  $this->description,
            'short_description' =>  $this->short_description,
            'youtube_link' =>  $this->youtube_link,
            'has_reservation' => $this->has_reservation($this->date),
            'prices' => [
                'day' => $this->dayPrice($this->date->format('l')),
                'month' => $this->monthPrice(),
                'hour' => $this->hourPrice(),
            ],
            'reservation' => $this->when($this->relationLoaded('reservations'), function () {
                return new ReservationResource($this->getReservationByDay($this->date));
            }),
            'images'  =>  $this->transformMediaCollection($this->getMedia('images')),
        ];
    }

    private function transformMediaCollection($media)
    {
        $gallery = [];
        /** @var Media $media */
        foreach ($media as $media) {
            $gallery[]  = $media->getFullUrl();
        }

        return $gallery;
    }
}
