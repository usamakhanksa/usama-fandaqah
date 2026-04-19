<?php

namespace App\Http\Resources\Booking;

use App\Http\Resources\Corneer\UnitMinifiedResource;
use App\Unit;
use Carbon\Carbon;
use App\SpecialPrice;
use App\UnitCategory;
use Carbon\CarbonPeriod;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\MediaLibrary\Models\Media;
use App\Http\Resources\ReservationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitCategoryResource extends JsonResource
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
            'team_name' => $this->team->name,
            'main_image'    =>  is_null($this->getFirstMediaUrl('main')) || empty($this->getFirstMediaUrl('main')) ? asset('images/no-hotel-placeholder.png') : $this->getFirstMediaUrl('main'),
            'short_description' =>  $this->short_description,
            'nights' => $diff_nights,
//            'prices' => [
//                'day' => $this->dayPrice($this->date->format('l')),
//                'month' => $this->monthPrice()
//            ],
//            'units' => UnitMinifiedResource::collection($this->units),
    //            'prices_per_day' => ($date_start != null && $date_end != null) ? $this->getDatesFromRange($date_start, $date_end, 'daily') : [],
                'daily_prices' => $this->dailyPrices(),
    //            'special_price' => $this->getSpecialPrices($this->id),
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

    /**
     * Fetch Special Prices According to Unit Category
     * this function will calculate the subtotal no matter how many special price is found
     * it will return array of keys holding sensitive information to be used in the front end
     * @param [type] $id
     * @return void
     */
    private function getSpecialPrices($id)
    {

        // Predefined  values
        $subtotalArr = [];

        $datesHasSpecialPrice = [];
        $datesDoesntHaveSpecialPrice = [];

        // convert dates

        $start_date = Carbon::parse(request()->date_start)->format('Y-m-d');
        $end_date = Carbon::parse(request()->date_end)->format('Y-m-d');



        $unitCategory = UnitCategory::find($id);



        $specialPrices = SpecialPrice::whereNull('deleted_at')
            ->where('team_id', $unitCategory->team_id)
            ->where('unit_category_id', $unitCategory->id)
            ->where('enabled', 1)
            ->whereIntersectsStartDate($start_date)
            ->whereIntersectsEndDate($end_date)
            ->get();

        /**
         * Getting the days according to period -_-
         * Based on start & end date
         */
        $periodFromIncomingDates = CarbonPeriod::dates(Carbon::parse($start_date), Carbon::parse($end_date)->subDay());
        $incomingDates = [];
        $incomingDatesPure = [];
        foreach ($periodFromIncomingDates as $date) {

            $skeleton  = new \stdClass();
            $skeleton->date = $date->format('Y-m-d');
            $skeleton->price = null;
            // $incomingDates[] = $date->format('Y-m-d');
            $incomingDates[] = $skeleton;
            $incomingDatesPure[] =  $date->format('Y-m-d');
        }

        // Category Prices Per Day
        $unitCategoryDaysPrices = $unitCategory->dailyByDayNamePrices();

        if (count($specialPrices)) {

            $specialPricesDates = [];
            $dates = [];
            $periods = [];
            foreach ($specialPrices as $specialPrice) {

                $periodFromSpecialPriceDates = CarbonPeriod::dates(Carbon::parse($specialPrice->start_date), Carbon::parse($specialPrice->end_date)->subDay());
                $periods[$specialPrice->id] = $periodFromSpecialPriceDates;


                // Chunk Dates First
                $dates_chunked = $periods[$specialPrice->id];
                foreach ($dates_chunked as $date) {
                    $specialPricesDates[$specialPrice->id][] = $date->format('Y-m-d');

                    $specialPriceObjectSkeleton = new \stdClass();
                    $specialPriceObjectSkeleton->date = Carbon::parse($date)->format('Y-m-d');
                    $specialPriceObjectSkeleton->price = $specialPrice->days_prices[Carbon::parse($date)->format('l')];
                    $dates[] = $specialPriceObjectSkeleton;
                }
            }



            $not_included = array_udiff($incomingDates, $dates, function ($obj_a, $obj_b) {
                return strcmp($obj_a->date, $obj_b->date);
            });



            foreach ($dates as $dtObj) {

                if (in_array($dtObj->date, $incomingDatesPure)) {

                    if (!is_null($dtObj->price)) {
                        $subtotalArr[] = $dtObj->price;
                    } else {
                        $subtotalArr[] = $unitCategoryDaysPrices[Carbon::parse($dtObj->date)->format('l')];
                        $specialPriceObjectSkeleton = new \stdClass();
                        $specialPriceObjectSkeleton->date = $dtObj->date;
                        $specialPriceObjectSkeleton->price = $unitCategoryDaysPrices[Carbon::parse($dtObj->date)->format('l')];
                        $datesDoesntHaveSpecialPrice[] = $specialPriceObjectSkeleton;
                    }
                }
            }

            if ($not_included) {
                foreach ($not_included as $obj) {
                    $subtotalArr[] = $unitCategoryDaysPrices[Carbon::parse($obj->date)->format('l')];
                    $specialPriceObjectSkeleton = new \stdClass();
                    $specialPriceObjectSkeleton->date = $obj->date;
                    $specialPriceObjectSkeleton->price = $unitCategoryDaysPrices[Carbon::parse($obj->date)->format('l')];
                    $datesDoesntHaveSpecialPrice[] = $specialPriceObjectSkeleton;
                }
            }

            $subtotal = array_sum($subtotalArr);
            $vatPercentage = getVatPercentageForUnit($unitCategory->team_id);
            $ewaTotal = getEwaPercentageForUnit($unitCategory->team_id) ?  getEwaTotalForUnit($subtotal, getEwaPercentageForUnit($unitCategory->team_id), false) : 0;
            $ttxTotal = getTourismPercentageForUnit($unitCategory->team_id) ? getTtxTotalForUnit($subtotal, getTourismPercentageForUnit($unitCategory->team_id), false) : 0;
            $vatTotal = getVatTotalForUnit($subtotal, $ewaTotal, $vatPercentage , false);

            $total_price = floatval($subtotal) + floatval($ewaTotal) + floatval($vatTotal) + floatval($ttxTotal);


            return [
                'status' => 'special_prices_found',
                'total_price' => $total_price,
                'subtotal' => $subtotal,
                'ewaTotal' => $ewaTotal,
                'vatTotal' => $vatTotal,
                'ttxTotal' => $ttxTotal,
            ];
        } else {
            return ['status' => 'no_special_prices',   'incomingDates' => $incomingDates];
        }
    }
}
