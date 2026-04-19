<?php

namespace App\Http\Resources\Corneer;

use App\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\MediaLibrary\Models\Media;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedUnitCategoriesResource extends JsonResource
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
            'team_id' => $this->team_id,
            'team_name' => $this->team->name,
            /**
             * @note : Note For Developers
             * attributesToArray why ? i need to get the json object complete for some usecase before translated through model $translatable
             * @http : https://laravel.com/docs/7.x/eloquent-serialization#serializing-to-arrays
             */
            'name' => $this->attributesToArray()['name'],
            'main_image' => is_null($this->getFirstMediaUrl('main')) || empty($this->getFirstMediaUrl('main')) ? asset('images/no-hotel-placeholder.png') : $this->getFirstMediaUrl('main'),
            'daily_prices' => $this->dailyPrices(),
            'show_in_website' => $this->show_in_website,
            'units_count' => DB::table('units')->where('status', 1)->where('enabled', 1)->where('team_id', $this->team_id)->count()
        ];
    }
}
