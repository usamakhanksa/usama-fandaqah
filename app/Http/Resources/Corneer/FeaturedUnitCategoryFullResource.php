<?php

namespace App\Http\Resources\Corneer;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;
use Vinkla\Hashids\Facades\Hashids;

class FeaturedUnitCategoryFullResource extends JsonResource
{

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
            'hash'  =>  Hashids::encode($this->id),
            'name' => $this->name,
            'team_id' => $this->team_id,
            'team_name' => $this->team->name,
            'status' => $this->status,
            'main_image'    =>  is_null($this->getFirstMediaUrl('main')) || empty($this->getFirstMediaUrl('main')) ? asset('images/no-hotel-placeholder.png') : $this->getFirstMediaUrl('main'),
            'description' =>  $this->description,
            'short_description' =>  $this->short_description,
            'images'  =>  $this->transformMediaCollection($this->getMedia('images')),
            'special_features'  =>  \App\UnitSpecialFeature::whereIn('id', explode(',', $this->special_features))->pluck('name')->toArray(),
            'general_features'  =>  \App\UnitGeneralFeature::whereIn('id', explode(',', $this->general_features))->pluck('name')->toArray(),
            'units' => UnitMinifiedResource::collection($this->units),
            'daily_prices' => $this->dailyPrices()

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
