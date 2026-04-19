<?php

namespace App\Http\Resources\Corneer;

use App\Unit;
use Carbon\Carbon;
use App\SpecialPrice;
use App\UnitCategory;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
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
            //            'unit_number' => $this->unit_number,
            'status' => $this->status,
            'main_image'    =>  is_null($this->getFirstMediaUrl('main')) || empty($this->getFirstMediaUrl('main')) ? asset('images/no-hotel-placeholder.png') : $this->getFirstMediaUrl('main'),
            'description' =>  $this->description,
            'short_description' =>  $this->short_description,
            'youtube_link' =>  $this->youtube_link,
            'nights' => $diff_nights,
            'prices' => [
                'day' => $this->dayPrice($this->date->format('l')),
                'month' => $this->monthPrice()
            ],
            'images'  =>  $this->transformMediaCollection($this->getMedia('images')),
            'special_features'  =>  \App\UnitSpecialFeature::whereIn('id', explode(',', $this->special_features))->pluck('name')->toArray(),
            'general_features'  =>  \App\UnitGeneralFeature::whereIn('id', explode(',', $this->general_features))->pluck('name')->toArray(),
            'units' => UnitMinifiedResource::collection($this->units),
            'prices_per_day' => ($date_start != null && $date_end != null) ? $this->getDatesFromRange($date_start, $date_end, 'daily') : [],
            'daily_prices' => $this->dailyPrices(),
            'special_price' => $this->getSpecialPrices($this->id),
            'youtube_link' => $this->youtube_link,
            'available_to_book' => $this->getAvailabilityForBooking($this->id,$date_start,$date_end)
        ];
    }

    public function getAvailabilityForBooking($cat_id , $start , $end){

        $team_id = \View::getShared()['currentTeamId'];

        $units = DB::table('units as u')
                        ->select('u.id as uid' , 'u.name as uname')
                        ->whereNull('u.deleted_at')
                        ->where('u.unit_category_id' , $cat_id)
                        ->where('u.team_id' , $team_id)
                        ->where('u.status' , '=' , 1)
                        ->get();

        $units_count = count($units);

        $period = CarbonPeriod::create($start,$end);

        $result = [];
        $days_list = [];

        if($units){
            foreach ($units as $unit){
                $days = [];
                $i = 1;
                foreach ($period as $key => $date){
                    $days_list[$key]['name'] = __($date->format('l'));
                    $days_list[$key]['number'] = $date->format('Y-m-d');
                    $days_list[$key]['is_today'] = $date->isToday();
                    $reservations =  DB::table('reservations as r')
                        ->leftJoin('customer as c','r.customer_id' , '=' ,'c.id')
                        ->leftJoin('highlights as h' , 'c.highlight_id' , '=' , 'h.id')
                        ->select( 'r.id as rid',
                            'r.date_in as rdi',
                            'r.date_out as rdo'
                        )
                        ->whereRaw('? between r.date_in and r.date_out', [$date->format('Y-m-d')])
                        ->where('r.date_out', '!=', $date->format('Y-m-d'))
                        ->whereNull('r.checked_out')
                        ->whereNull('r.deleted_at')
                        ->whereIn('r.status'  , ['confirmed' , 'awaiting-payment'])
                        ->where('r.team_id' , $team_id)
                        ->where('r.unit_id' , $unit->uid)
                        ->get();

                    if (count($reservations)) {

                        if ($key == 0) {
                            $days[$key] = $this->formDay($date, $reservations, $unit);
                        } elseif ($date->format('Y-m-d') >= $reservations[0]->rdi) {
                            $days[$key] = $this->formDay($date, $reservations, $unit);
                        }
                    } else {
                        $days[$key] = $this->formDay($date, $reservations, $unit);
                    }
                }

                $result[] = [
                    'id' => $unit->uid,
                    'uname' => json_decode($unit->uname)->ar,
                    'days' => $days,
                ];
            }
        }

        $reservationsCounter = [] ;
        foreach ($result as $item){
            $item = (object) $item;
            foreach ($item->days as $obj){
                $obj = (object) $obj;
                $reservationsCounter[$obj->number][]  = $obj->reservations;
            }
        }

        $notAvailabileDates = [] ;
        $availability = [];
        foreach ($reservationsCounter as $key => $value){

            if(array_sum($value) >= $units_count){
                $notAvailabileDates[] = Carbon::parse($key)->format('Y-m-d') ;
            }
        }
        return count($notAvailabileDates) ? false : true;
    }

    protected function formDay($date, $reservations, $unit)
    {
        $day['is_today'] = $date->isToday();
        $day['number'] = $date->format('Y-m-d');
        $day['reservations'] = count($reservations);
        return $day;
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

                $periodFromSpecialPriceDates = CarbonPeriod::dates(Carbon::parse($specialPrice->start_date), Carbon::parse($specialPrice->end_date));
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

                // Filter Dates Based On The Group
                // foreach ($incomingDates as $date) {
                //     if (in_array($date, $specialPricesDates[$specialPrice->id])) {
                //         if (!is_null($specialPrice->days_prices[Carbon::parse($date)->format('l')])) {
                //             $subtotalArr[] = $specialPrice->days_prices[Carbon::parse($date)->format('l')];
                //             $specialPriceObjectSkeleton = new \stdClass();
                //             $specialPriceObjectSkeleton->date = $date;
                //             $specialPriceObjectSkeleton->price = $specialPrice->days_prices[Carbon::parse($date)->format('l')];

                //             $datesHasSpecialPrice[] = $specialPriceObjectSkeleton;
                //         } else {
                //             $subtotalArr[] = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
                //             $specialPriceObjectSkeleton = new \stdClass();
                //             $specialPriceObjectSkeleton->date = $date;
                //             $specialPriceObjectSkeleton->price = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
                //             $datesDoesntHaveSpecialPrice[] = $specialPriceObjectSkeleton;
                //         }
                //     }else{
                //         $subtotalArr[] = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
                //         $specialPriceObjectSkeleton = new \stdClass();
                //         $specialPriceObjectSkeleton->date = $date;
                //         $specialPriceObjectSkeleton->price = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
                //         $datesDoesntHaveSpecialPrice[] = $specialPriceObjectSkeleton;
                //     }
                // }
            }



            $not_included = array_udiff($incomingDates, $dates, function ($obj_a, $obj_b) {
                return strcmp($obj_a->date, $obj_b->date);
            });



            foreach ($dates as $dtObj) {

                // return response()->json(in_array($dtObj->date, $incomingDatesPure));


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


            // foreach ($incomingDates as $inObj) {



            //         foreach($dates as $dtObj){

            //             if(strcmp($dtObj->date , $inObj->date)){
            //                 $data[] = $dtObj;

            //             }
            //         }

            //         // if (in_array($date, $dates)) {
            //         //     if (!is_null($specialPrice->days_prices[Carbon::parse($date)->format('l')])) {
            //         //         $subtotalArr[] = $specialPrice->days_prices[Carbon::parse($date)->format('l')];
            //         //         $specialPriceObjectSkeleton = new \stdClass();
            //         //         $specialPriceObjectSkeleton->date = $date;
            //         //         $specialPriceObjectSkeleton->price = $specialPrice->days_prices[Carbon::parse($date)->format('l')];

            //         //         $datesHasSpecialPrice[] = $specialPriceObjectSkeleton;
            //         //     } else {
            //         //         $subtotalArr[] = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
            //         //         $specialPriceObjectSkeleton = new \stdClass();
            //         //         $specialPriceObjectSkeleton->date = $date;
            //         //         $specialPriceObjectSkeleton->price = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
            //         //         $datesDoesntHaveSpecialPrice[] = $specialPriceObjectSkeleton;
            //         //     }
            //         // }



            // }




            // $subtotalArr[] = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
            // $specialPriceObjectSkeleton = new \stdClass();
            // $specialPriceObjectSkeleton->date = $date;
            // $specialPriceObjectSkeleton->price = $unitCategoryDaysPrices[Carbon::parse($date)->format('l')];
            // $datesDoesntHaveSpecialPrice[] = $specialPriceObjectSkeleton;


            // return response()->json($subtotalArr);

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
