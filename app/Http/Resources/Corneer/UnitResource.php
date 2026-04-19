<?php

namespace App\Http\Resources\Corneer;

use App\Http\Resources\ReservationResource;
use App\Unit;
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
        $date_start = Carbon::parse(request()->date_start);
        $date_end = Carbon::parse(request()->date_end);
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

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
            'reservations' => [
                'start_date' => $date_start->format('d-m-Y'),
                'end_date' => $date_end->format('d-m-Y'),
                'days' => $diff_days,
                'nights' => $diff_nights,
                'prices' => $this->getDatesFromRange($date_start, $date_end),
            ],
            'prices' => [
                'day' => $this->dayPrice($this->date->format('l')),
                'month' => $this->monthPrice(),
                'hour' => $this->hourPrice(),
            ],
            'reservation' => $this->when($this->relationLoaded('reservations'), function () {
                return new ReservationResource($this->getReservationByDay($this->date));
            }),
            'images'  =>  $this->transformMediaCollection($this->getMedia('images')),

            'reservations_date' => $this->getReservationsDates(),
            'options'   =>  $this->options(),
            'special_features'  =>  \App\UnitSpecialFeature::whereIn('id', explode(',', $this->special_features))->pluck('name')->toArray(),
            'general_features'  =>  \App\UnitGeneralFeature::whereIn('id', explode(',', $this->general_features))->pluck('name')->toArray(),
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
