<?php

namespace Surelab\Units\Http\Controllers;

use App\Team;
use App\Term;
use App\Unit;
use App\Offer;
use App\Activity;
use Carbon\Carbon;
use App\UnitOption;
use App\Reservation;
use App\SpecialPrice;
use App\UnitCategory;
use Carbon\CarbonPeriod;
use App\UnitGeneralFeature;
use App\UnitSpecialFeature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CustomPagination;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UnitResource;
use App\Http\Resources\UnitCategoryMinifiedResource;
use App\Http\Resources\Index\ReservationsTable\UnitsResource;
use App\Http\Resources\UnitCategoryForOffersAndSpecialPrices;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\OverrideNovaResources\OverrideUnitResource;
use App\Http\Resources\OverrideNovaResources\OverrideUnitOptionResource;
use App\Http\Resources\OffersAndSpecialPrices\ModelDatesMinifiedResource;
use App\Http\Resources\OverrideNovaResources\OverrideUnitFeatureResource;
use App\Http\Resources\OverrideNovaResources\OverrideUnitCategoryResource;

class UnitController extends Controller
{

    /**
     * Reservations Table Module
     * @note :  total refactor through telescope
     *
     * @param Request $request
     * @return void
     */


        public function staahAvailability(Request $request)
        {

    $prices = [
        'sunday_day_price' => $request['prices']['sunday_price'],
        'monday_day_price' => $request['prices']['monday_price'],
        'tuesday_day_price' => $request['prices']['tuesday_price'],
        'wednesday_day_price' => $request['prices']['wednesday_price'],
        'thursday_day_price' => $request['prices']['thursday_price'],
        'friday_day_price' => $request['prices']['friday_price'],
        'saturday_day_price' => $request['prices']['saturday_price'],
    ];

    $static_price = $request['static_price'] ;

     $virtual_room = $request['virtual_room'];



    $unit_category_id = $request['category']['id'];
    // get the unit category where the id is the same as the request
    $unit_category = UnitCategory::where('id', $unit_category_id)->first();
    $enable_staah_pricing = $unit_category->enable_staah_pricing;
    $unit_category->virtual_room = $virtual_room;
    $unit_category->save();

    $start_date = $request['start_date'];
    $end_date = $request['end_date'];
        if($end_date){
    $now = Carbon::parse($start_date);
    $after_3_months = Carbon::parse($end_date);
        }else{
    $now = Carbon::now();
    $after_3_months = Carbon::now()->addMonths(3);
        }



    $roomsData = [];
    for ($date = $now->copy(); $date->lte($after_3_months); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;

    }
    $room = [];
    foreach ($roomsData as $date) {

$specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();




        $from = $date['from'];
        $to = $date['to'];
        $dayName = Carbon::parse($from)->format('l');
        $day_Name_price = strtolower($dayName) . '_day_price';
        $price = $prices[$day_Name_price];


        if($price == null){
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];

                 }else{

                $price = $request['category']['prices'][$day_Name_price];
                       }

        }



        // get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true

        $all_units = Unit::where('unit_category_id', $unit_category_id)
        ->where('status', '!=', 3)
        ->where('deleted_at', null)->pluck('id')->toArray();
        $available_arr = [];
        foreach($all_units as $unit_id){
            $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
            if(!$hasIntersectionWorkable){
                array_push($available_arr, $unit_id);
            }



        }
        $available_units = count($available_arr);
        // dd($available_units);
        // if this team have tax and ewa
        $team_id = auth()->user()->current_team_id;
        $ewa_percantage = getEwaPercentageForUnit($team_id);
        $tax_percentage = getVatPercentageForUnit($team_id);
        $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
        $tax_total = getVatTotalForUnit($price,$ewa_total ,$tax_percentage);
        $total_price = $price + $ewa_total + $tax_total;
if($enable_staah_pricing){


    $data = [


                "roomid" => $request['category']['id'],
                "date" => [
                    [
                        "from" => $from,
                        "to" => $from,
                        "rate" => [
                            [
                                "rateplanid" => "DEF"
                            ]
                        ],
                        "roomstosell" => $available_units + $virtual_room,
                        "price" => [
                            [
                                "NumberOfGuests" => "1",
                                "value" => $total_price
                            ]
                        ],

                    ]
                ]


    ];
}else{
    $data = [


        "roomid" => $request['category']['id'],
        "date" => [
            [
                "from" => $from,
                "to" => $from,
                "rate" => [
                    [
                        "rateplanid" => "DEF"
                    ]
                ],
                "roomstosell" => $available_units + $virtual_room,


            ]
        ]


    ];
}
        // push the data to the room array
        array_push($room, $data);

        $curl = curl_init();


    }

    $data = [
        "hotelid" => $team_id,
        "room" => $room
    ];
    $url = env('STAAH_MEDIATOR_API_URL') . '/api/v1/availability';
    $curl = curl_init();

    curl_setopt_array($curl, array(
       CURLOPT_URL => $url,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => '',
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 0,
       CURLOPT_FOLLOWLOCATION => true,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => 'POST',
       CURLOPT_POSTFIELDS => json_encode($data),
       CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json'
       ),
    ));

    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);

// from special_prices table get the special price with unit catgory id = $unit_category_id and the start_date is greater than or equal to now date and enab



}
    public function updateMytravelSpecialPrice($request, $old_date_start, $old_date_end){
        $unit_category_id = $request->category;
        $now = $old_date_start;
        $after_3_months = $old_date_end;

        $now = Carbon::parse($now);
        $after_3_months = Carbon::parse($after_3_months);
        $roomsData = [];
        for ($date = $now->copy(); $date->lte($after_3_months); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;

        }
        $team_id = auth()->user()->current_team_id;
        foreach ($roomsData as $date) {

            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
                if($price == null){
                    $category = UnitCategory::where('id', $unit_category_id)->first();
                    $price = $category[$day_Name_price];
                }
            }else{
                $category = UnitCategory::where('id', $unit_category_id)->first();
                $price = $category[$day_Name_price];
            }
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();

            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }
            }

            $available_units = count($available_arr);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;

            $data = [

                'category_id' => $unit_category_id,
                'start_date' => $from,
                'end_date' => $from,
                'price' => $total_price,
                'active' => 1,
                'note_to_customer' => '',
                'note_to_admin' => '',
                'is_instant' => 1,
                'number' => $available_units,
                'create_user' => 1,
                'update_user' => 1,
                'status' => "update_special_price",

            ];
            // dd($data);
            $curl = curl_init();
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/update-special-price';
            $key = env('MY_TRAVEL_KEY');

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'key: ' . $key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

        }
    }
    public function syncMytravelSpecialPrice($request){

        $unit_category_id = $request->category;
        $now = $request->start_date;
        $after_3_months = $request->end_date;

        $now = Carbon::parse($now);
        $after_3_months = Carbon::parse($after_3_months);
        $roomsData = [];
        for ($date = $now->copy(); $date->lte($after_3_months); $date->addDay()) {
        $dayData = [
            'from' => $date->format('Y-m-d'),
            'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
            // You can add more fields here as needed
        ];
        $roomsData[] = $dayData;

        }
        $team_id = auth()->user()->current_team_id;
        foreach ($roomsData as $date) {

            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_day_price';
            if($specialPrice){
                $price = $specialPrice->days_prices[$dayName];
                if($price == null){
                    $category = UnitCategory::where('id', $unit_category_id)->first();
                    $price = $category[$day_Name_price];
                }
            }else{
                $category = UnitCategory::where('id', $unit_category_id)->first();
                $price = $category[$day_Name_price];
            }
            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();

            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }
            }

            $available_units = count($available_arr);
            $ewa_percantage = getEwaPercentageForUnit($team_id);
            $tax_percentage = getVatPercentageForUnit($team_id);
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;

            $data = [

                'category_id' => $unit_category_id,
                'start_date' => $from,
                'end_date' => $from,
                'price' => $total_price,
                'active' => 1,
                'note_to_customer' => '',
                'note_to_admin' => '',
                'is_instant' => 1,
                'number' => $available_units,
                'create_user' => 1,
                'update_user' => 1,
                'status' => "update_special_price",

            ];
            // dd($data);
            $curl = curl_init();
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/update-special-price';
            $key = env('MY_TRAVEL_KEY');

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 400,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'key: ' . $key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

        }

    }

public function staahAvailabilitySpecialPrice( $request)
{
// dd($request);
$unit_category_id = $request->category;

$now = $request->start_date;
$after_3_months = $request->end_date;
// change the date to carbon
$now = Carbon::parse($now);
$after_3_months = Carbon::parse($after_3_months);


$roomsData = [];
for ($date = $now->copy(); $date->lte($after_3_months); $date->addDay()) {
$dayData = [
    'from' => $date->format('Y-m-d'),
    'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
    // You can add more fields here as needed
];
$roomsData[] = $dayData;

}
$room = [];
foreach ($roomsData as $date) {
// get from special_prices table of the unit category id = $unit_category_id and the start_date is greater than or equal to $date['from'] and the end_date is less than or equal to $date['to'] and enabled is true
$specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();




$from = $date['from'];
$to = $date['to'];
$dayName = Carbon::parse($from)->format('l');
$day_Name_price = strtolower($dayName) . '_day_price';
if($specialPrice){
    $price = $specialPrice->days_prices[$dayName];
}else{

    $price = $request['category']['prices'][$day_Name_price];
}
// get the number of units that is no reservation for this from to date and the unit category id is the same as the request and available to sync is true

$all_units = Unit::where('unit_category_id', $unit_category_id)
->where('status', '!=', 3)
->where('deleted_at', null)->pluck('id')->toArray();
$available_arr = [];
foreach($all_units as $unit_id){
    $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($from));
    if(!$hasIntersectionWorkable){
        array_push($available_arr, $unit_id);
    }



}
$available_units = count($available_arr);
// dd($available_units);
// if this team have tax and ewa
$team_id = auth()->user()->current_team_id;
$ewa_percantage = getEwaPercentageForUnit($team_id);
$tax_percentage = getVatPercentageForUnit($team_id);
$ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
$tax_total = getVatTotalForUnit($price,$ewa_total ,$tax_percentage);
$total_price = $price + $ewa_total + $tax_total;

$data = [


            "roomid" => $unit_category_id,
            "date" => [
                [
                    "from" => $from,
                    "to" => $from,
                    "rate" => [
                        [
                            "rateplanid" => "DEF"
                        ]
                    ],
                    "roomstosell" => $available_units,
                    "price" => [
                        [
                            "NumberOfGuests" => "1",
                            "value" => $total_price
                        ]
                    ],

                ]
            ]


];
// push the data to the room array
array_push($room, $data);

$curl = curl_init();


}

$data = [
"hotelid" => $team_id,
"room" => $room
];
$url = env('STAAH_MEDIATOR_API_URL') . '/api/v1/availability';
$curl = curl_init();

curl_setopt_array($curl, array(
   CURLOPT_URL => $url,
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_CUSTOMREQUEST => 'POST',
   CURLOPT_POSTFIELDS => json_encode($data),
   CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
   ),
));

$response = curl_exec($curl);

curl_close($curl);




}



    public function reservationsTable(Request $request)
    {

        // dd('here');
        $date = Carbon::parse($request->start_date);
        $now = Carbon::now()->startOfDay();
        $unit_category_id = $request->get('unit_category_id') ?? null;
       
        $alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
        $units = Unit::whereEnabled(true)
            ->when($unit_category_id && $unit_category_id != 'null'  , function($query) use($unit_category_id){
                $query->where('unit_category_id' , $unit_category_id);
            })
            ->with(['reservations' => function ($query){
            $query->whereNull('checked_out')
                ->whereNotIn('status', ['timeout', 'canceled'])
                ->where('team_id', auth()->user()->current_team_id)
                ->with(['customer', 'customer.highlight', 'customer.nationality']);
        }])->get()
            ->sortBy(function ($i) use ($alphabet) {
                return trim(str_replace($alphabet, '', $i['unit_number']));
            });

        $dayName = $date->format('l');

        $period = CarbonPeriod::create($request->start_date, 14);
        $first_date_in_period = new \DateTime(Carbon::parse($request->start_date)->format('Y-m-d'));
        $last_date_in_period = new \DateTime(Carbon::parse($request->start_date)->addDays(14)->format('Y-m-d'));

        $result = [];
        $days_list = [];

        /** @var Unit $unit */
        foreach ($units as $unit) {
            $days = [];
            $i = 1;
            foreach ($period as $key => $date) {

                $days_list[$key]['name'] = __($date->format('l'));
                $days_list[$key]['number'] = $date->format('Y-m-d');
                $days_list[$key]['is_today'] = $date->isToday();

                $reservations = $this->getReservationsDay($unit, $date);

                if (count($reservations)) {

                    if ($key == 0) {
                        $days[$key] = $this->getDay($date, $reservations, $unit);
                        $date_in = new \DateTime($reservations[0]->date_in);
                        $date_out = new \DateTime($reservations[0]->date_out);
                        if (strtotime($reservations[0]->date_out)  >=  $last_date_in_period->getTimestamp()) {
                            $days[$key]['colspan'] = 'day-14';
                        } else {
                            if (strtotime($reservations[0]->date_in) <= $first_date_in_period->getTimestamp() && strtotime($reservations[0]->date_out) < $last_date_in_period->getTimestamp()) {
                                $days[$key]['colspan'] = 'day-' . $date_out->diff($first_date_in_period)->days;
                            }
                        }
                    } elseif ($date->format('Y-m-d') == $reservations[0]->date_in) {

                        $days[$key] = $this->getDay($date, $reservations, $unit);
                        $date_in = new \DateTime($reservations[0]->date_in);
                        $date_out = new \DateTime($reservations[0]->date_out);

                        if (strtotime($reservations[0]->date_in) > $last_date_in_period->getTimestamp()) {
                            $days[$key]['colspan'] = 'day-' . $last_date_in_period->diff($date_in)->days;
                        } else {
                            if ($date_out->diff($date_in)->days == 1) {
                                $days[$key]['colspan'] = 'day-1';
                            } else {
                                $days[$key]['colspan'] = 'day-' . $date_out->diff($date_in)->days;

                                if ($reservations[0] && $date_out->diff($date_in)->days > 15) {
                                    $days[$key]['colspan'] .= ' day-14';
                                }
                            }
                        }
                    }

                    $days[$i] = $this->getDay($date, [], $unit);
                    $days[$i]['colspan'] = 'block-me';
                } else {
                    $days[$key] = $this->getDay($date, $reservations, $unit);
                    $days[$key]['colspan'] = 'day-1 alpha';
                }
                if ($i <= 13) {
                    $i++;
                }
            }

            $result[] = [
                'id' => $unit->id,
                'name' => $unit->name,
                'unit_number' => $unit->unit_number,
                'status' => $unit->status,
                'days' => $days,
            ];
        }

        if (empty($days_list)) {
            foreach ($period as $key => $date) {
                $days_list[$key]['name'] = __($date->format('l'));
                $days_list[$key]['number'] = $date->format('Y-m-d');
                $days_list[$key]['is_today'] = $date->isToday();
            }
        }

        return response([
            'data' => $result,
            'meta' => ['days' => $days_list],
            'first_date_in_period' => $days_list[0]['number'],
            'last_date_in_period' => $days_list[13]['number']
        ]);
    }

    protected function getReservationsDay($unit, $date)
    {
        $reservations = collect();

        foreach ($unit->reservations as $reservation) {
            if ($date->format('Y-m-d') >= $reservation->date_in && $date->format('Y-m-d') < $reservation->date_out) {
                $reservations->push($reservation);
            }
        }
        return $reservations;
    }

    protected function getDay($date, $reservations, $unit)
    {

        $unit_category_id = $unit->unit_category_id;
        $team_id = $unit->team_id;
        // check if the unit has special price
        $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date->format('Y-m-d'))->where('end_date', '>=', $date->format('Y-m-d'))->where('enabled', true)->first();
        if($specialPrice){
            $day_prices = $specialPrice->days_prices;
            // get the name of the date to much the column name in the unit category
            $dayName = $date->format('l');
            $day_price = number_format($day_prices[$dayName], 2);
            if($day_price == 0){
                $day_price = number_format($unit->dayPrice($date->format('l')), 2);
            }


        }else{
            $day_price = number_format($unit->dayPrice($date->format('l')), 2);
        }
        $day['is_today'] = $date->isToday();
        $day['number'] = $date->format('Y-m-d');
        $day['prices'] = [

            'day' => $day_price,
        ];
        $day['reservations'] = $reservations;

        return $day;
    }

    public function housekeeping()
    {

        return [];
    }

    public function get_units(UnitCategory $category)
    {
        return  UnitResource::collection(
            $category->unitsForUpdateModal()->get()
        );
    }

    public function update_selected_units(Request $request)
    {
        if (!empty($request->get('selected_units'))) {
            $units = Unit::findMany($request->get('selected_units'));
            /** @var Unit $unit */
            foreach ($units as $unit) {
                $unit_category = $unit->unit_category;

                //                if(empty($unit->name)){
                //                    $unit->name = $unit_category->getTranslations('name');
                //                }

                // by default it will update all units with section name
                $unit->name = $unit_category->getTranslations('name');
                // $unit->status = true;

                $unit->general_features = $unit_category->general_features;
                $unit->special_features = $unit_category->special_features;

                $unit->sunday_day_price = $unit_category->sunday_day_price;
                $unit->monday_day_price = $unit_category->monday_day_price;
                $unit->tuesday_day_price = $unit_category->tuesday_day_price;
                $unit->wednesday_day_price = $unit_category->wednesday_day_price;
                $unit->thursday_day_price = $unit_category->thursday_day_price;
                $unit->friday_day_price = $unit_category->friday_day_price;
                $unit->saturday_day_price = $unit_category->saturday_day_price;

                $unit->month_price = $unit_category->month_price;

                $unit->min_sunday_day_price = $unit_category->min_sunday_day_price;
                $unit->min_monday_day_price = $unit_category->min_monday_day_price;
                $unit->min_tuesday_day_price = $unit_category->min_tuesday_day_price;
                $unit->min_wednesday_day_price = $unit_category->min_wednesday_day_price;
                $unit->min_thursday_day_price = $unit_category->min_thursday_day_price;
                $unit->min_friday_day_price = $unit_category->min_friday_day_price;
                $unit->min_saturday_day_price = $unit_category->min_saturday_day_price;
                $unit->min_month_price = $unit_category->min_month_price;

                $unit->save();
            }
            $unit_category_id = $unit_category->id;
                //get team_id from table unit_categories where id = $unit_category_id
                $team_id = DB::table('unit_categories')->where('id', $unit_category_id)->value('team_id');
                //get mytravel_hotel_id from table teams where id = $team_id
                $mytravel_hotel_id = DB::table('teams')->where('id', $team_id)->value('mytravel_hotel_id');
                //get from table unit_categories where id = $unit_category_id
                if($mytravel_hotel_id != null){
                    $unit_category = DB::table('unit_categories')->where('id', $unit_category_id)->first();
                    //get count number of units with the same category_id from table units where category_id = $unit_category_id and team_id = $team_id
                    $units_count = DB::table('units')
                        ->where('unit_category_id', $unit_category->id)
                        ->where('enabled', 1)
                        ->where('status','!=', 3)
                        ->where('team_id', $team_id)
                        ->count();
                    //get all general_features from table unit_gategories where id = $unit_category_id
                    $general_features = $unit_category->general_features;
                    //get all special_features from table unit_gategories where id = $unit_category_id
                    $special_features = $unit_category->special_features;

                    $general_feature_ids = explode(',', $general_features);

                    $general_feature_names = [];

                    foreach ($general_feature_ids as $general_feature_id) {

                        $general_feature = UnitGeneralFeature::where('id', $general_feature_id)
                            ->where('team_id', $team_id)
                            ->first();

                        if ($general_feature != null) {

                            $general_feature_att = $general_feature->getAttributes();

                            $name = json_decode($general_feature_att['name'], true)['en'];

                            if ($general_feature) {

                                $general_feature_names[] = $name;

                            }
                        }
                    }

                    $special_feature_ids = explode(',', $special_features);

                    $special_feature_names = [];

                    foreach ($special_feature_ids as $special_feature_id) {

                        $special_feature = UnitSpecialFeature::where('id', $special_feature_id)
                            ->where('team_id', $team_id)
                            ->first();
                        if ($special_feature != null) {

                            $special_feature_att = $special_feature->getAttributes();

                            $special_feature_att = $special_feature->getAttributes();

                            $name = json_decode($special_feature_att['name'], true)['en'];

                            if ($special_feature) {

                                $special_feature_names[] = $name;

                            }
                        }
                    }

                    $price =  $unit_category->sunday_day_price;
                    $ewa_percantage = getEwaPercentageForUnit($team_id) ? getEwaPercentageForUnit($team_id) : 0;
                    $tax_percentage = getVatPercentageForUnit($team_id) ? getVatPercentageForUnit($team_id) : 0;
                    $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
                    $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
                    $total_price = $price + $ewa_total + $tax_total;
                    $name = json_decode($unit_category->name, true);

                    // Prepare request data for each category
                    $requestData = [

                        'title' => $name['en'],
                        'number' => $units_count,
                        'content' => $unit_category->description,
                        'video' => $unit_category->youtube_link,
                        'parent_id' => $mytravel_hotel_id,
                        'price' => $total_price,
                        'category_id' => $unit_category->id,
                        "beds" => "1",
                        "size" => "10",
                        "adults" => "2",
                        "children" => "1",
                        "create_user" => $team_id,
                        'general_features' => json_encode($general_feature_names),
                        'special_features' => json_encode($special_feature_names),
                        'status' => $unit_category->status,
                    ];
                    $curl = curl_init();
                    $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/updateAttributes';
                    $key = env('MY_TRAVEL_KEY');
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 400,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($requestData),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'key: ' .$key
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);


                    $inventory_data = [];
                    $start = Carbon::now()->format('Y-m-d');
                    $end = Carbon::now()->addDay()->format('Y-m-d');
                    $team = Team::where('id', $team_id)->first();
                    $unit_category = UnitCategory::where('id', $unit_category_id)->first();
                    for ($i = 0; $i < 90; $i++) {
                        $dayName = Carbon::parse($start)->format('l');
                        $day_Name_price = strtolower($dayName) . '_day_price';
                        $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $start)->where('end_date', '>=', $start)->where('enabled', true)->first();
                        if($specialPrice){
                            $inventory_price = $specialPrice->days_prices[$dayName];
                            if($inventory_price == null){
                                $inventory_price = $unit_category->$day_Name_price;
                            }
                        }else{
                            $inventory_price = $unit_category->$day_Name_price;
                        }

                        // $inventory_price = $unit_category->$day_Name_price;

                        $ewa_percantage = getEwaPercentageForUnit($team_id);
                        $tax_percentage = getVatPercentageForUnit($team_id);
                        $ewa_total = getEwaTotalForUnit($inventory_price, $ewa_percantage);
                        $tax_total = getVatTotalForUnit($inventory_price, $ewa_total, $tax_percentage);
                        $total_price = $inventory_price + $ewa_total + $tax_total;
                        $available_units = Unit::where('unit_category_id', $unit_category->id)->where('enabled', 1)->where('status', 1)->whereDoesntHave('reservations', function ($query) use ($start, $end) {
                            $query->where('date_in', '<', $end)->where('date_out', '>', $start);
                        })->count();
                        $inventory_data[] = [
                            'date_start' => $start,
                            'date_end' => $end,
                            'price' => $total_price,
                            'available_units' => $available_units,
                        ];
                        $start = $end;
                        $end = Carbon::parse($end)->addDay()->format('Y-m-d');
                    }
                    // dd($inventory_data);
                    $price =  $unit_category->sunday_day_price;
                    $ewa_percantage = getEwaPercentageForUnit($team_id) ? getEwaPercentageForUnit($team_id) : 0;
                    $tax_percentage = getVatPercentageForUnit($team_id) ? getVatPercentageForUnit($team_id) : 0;
                    $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
                    $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
                    $total_price = $price + $ewa_total + $tax_total;
                    $count = Unit::where('unit_category_id', $unit_category->id)->where('status','!=',3)->where('enabled', 1)->count();
                    $unit = DB::table('unit_categories')->where('id', $unit_category->id)->first();
                    $unit_name =  json_decode($unit->name, true);

                    $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/update';
                    $key = env('MY_TRAVEL_KEY');

                    $data = [
                            'title' => $unit_name['en'],
                            'title_ar' => $unit_name['ar'],
                            'number' => $count,
                            'content' => $unit_category->description,
                            'video' => $unit_category->youtube_link,
                            'parent_id' => $mytravel_hotel_id,
                            'price' => $total_price,
                            'category_id' => $unit_category->id,
                            'status' => $unit_category->status,
                            "beds" => $unit_category->number_of_beds,
                            "unit_size" => $unit_category->unit_size,
                            "adults" => $unit_category->number_of_adults,
                            "children" => $unit_category->number_of_children,
                            "create_user" => $team_id,
                            "inventory" => $inventory_data
                    ];

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 400,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($data),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'key: ' .$key
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);
                    }

        }
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getCategories(Request $request)
    {
        return UnitCategoryMinifiedResource::collection(UnitCategory::where('status', 1)->whereNull('deleted_at')->whereHasUnits()->get());
    }

    /**
     * @param UnitCategory $category
     * @return UnitCategoryMinifiedResource
     */
    public function getUnitCategory(UnitCategory $category)
    {
        return new  UnitCategoryMinifiedResource($category);
    }


    /**
     * Get the appropriate resources collection based on model
     * @param Request $request
     * @param $model_name
     * @return AnonymousResourceCollection
     */
    public function getResources(Request $request, $model_name)
    {
        $model = app("App\\" . $model_name);
        $enviroment = app()->environment();

        switch ($model_name) {

            case 'Unit':
                $alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
                $units = $model::whereNull('deleted_at')
                    ->get()
                    ->sortBy(function ($i) use ($alphabet) {
                        return trim(str_replace($alphabet, '', $i['unit_number']));
                    });
                return OverrideUnitResource::collection((new CustomPagination($units))->paginate(20))->additional(['general' => ['url' => url('/'), 'env' => $enviroment]]);
                break;

            case 'UnitCategory':
                return OverrideUnitCategoryResource::collection($model::with('available_to_sync_units')->whereNull('deleted_at')->paginate(50))->additional(['general' => ['url' => url('/'), 'env' => $enviroment]]);
                break;

            case 'UnitSpecialFeature':
            case 'UnitGeneralFeature':
                return OverrideUnitFeatureResource::collection($model::orderBy('order', 'desc')->paginate(50))->additional(['general' => ['url' => url('/')]]);
                break;

            case 'UnitOption':
                return OverrideUnitOptionResource::collection(UnitOption::paginate(50));
                break;

            case 'Offer':
                return Offer::whereNull('deleted_at')->where('team_id', auth()->user()->current_team_id)->orderBy('created_at', 'desc')->paginate(20);
                break;

            case 'SpecialPrice':
                return SpecialPrice::whereNull('deleted_at')->where('team_id', auth()->user()->current_team_id)->orderBy('created_at', 'desc')->paginate(20);
                break;

            case 'Activity':
                $logs = Activity::with('subject', 'causer')->where('team_id', auth()->user()->current_team_id)->orderBy('created_at', 'desc')->get();
                $holder = [];
                if ($logs) {
                    foreach ($logs as $log) {
                        if (($log->subject_type == 'App\Transaction' && $log->subject && $log->subject->is_public == 0)
                            || ($log->subject_type == 'App\Reservation'
                                && $log->properties
                                &&  isset($log->properties['old'])
                                && is_null($log->properties['old']['status']))
                        ) {
                            continue;
                        } else {
                            $holder[] = $log;
                        }
                    }
                }
                return (new CustomPagination($holder))->paginate(20);
                break;
        }
    }

    /**
     * Delete Resource
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteResource(Request $request)
    {

        $model_name = $request->get('model');
        $model_id   = $request->get('id');
        $model = app("App\\" . $model_name);
        if ($model->destroy($model_id)) {
            return response()->json(['status' => 'model_destroyed']);
        } else {
            return response()->json(['status' => 'something_wrong']);
        }
    }

    /**
     * Get list of categories to be used in special prices and
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getOffersAndSpecialPricesCategories(Request $request)
    {
        return UnitCategoryForOffersAndSpecialPrices::collection(UnitCategory::where('status', 1)->whereNull('deleted_at')->whereHasUnits()->get());
    }


    /**
     * Addition of offers is now validated by dates and intersections of unit categories
     * @param $cat_ids
     * @param $start_date
     * @param $end_date
     * @return bool
     */
    private function checkIfOfferObjectIsFoundBeforeCreate($cat_ids, $start_date, $end_date)
    {
        $offer = Offer::whereNull('deleted_at')
            ->where('team_id', auth()->user()->current_team_id)
            ->whereIntersectsStartDate($start_date)
            ->first();

        if ($offer) {
            if (count(array_intersect($cat_ids, $offer->categories_ids)) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Store new offer
     * @param Request $request
     * @return JsonResponse
     */
    public function storeOffer(Request $request)
    {

        $data = (object) $request->get('offer');


        $offer = new Offer();
        $offer->name = $data->offer_name;
        $offer->discount_type = $data->discount_type;
        $offer->discount_amount = $data->discount_amount;
        $offer->categories = $data->categories;
        $offer->categories_ids = $request->get('categories_ids');
        $offer->days = $data->days;
        $offer->enabled = $data->enable_offer;
        //        $offer->start_date = Carbon::make($data->selectedDate['start']);
        $offer->start_date = date('Y-m-d', strtotime($data->selectedDate['start']));
        //        $offer->end_date = Carbon::make($data->selectedDate['end']);
        $offer->end_date = date('Y-m-d', strtotime($data->selectedDate['end']));


        //        dd($this->checkIfOfferObjectIsFoundBeforeCreate($offer->categories_ids, $offer->start_date, $offer->end_date));
        //        if (!$this->checkIfModelObjectIsFoundBeforeCreate('offer', null, $offer->start_date, $offer->end_date)) {
        if (!$this->checkIfOfferObjectIsFoundBeforeCreate($offer->categories_ids, $offer->start_date, $offer->end_date)) {
            if ($offer->save()) {
                return response()->json(['status' => 'offer_created'], 201);
            } else {
                return response()->json(['status' => 'something_wrong'], 500);
            }
        } else {
            return response()->json(['status' => 'offer_is_found_before']);
        }
    }


    private function checkIfOfferObjectIsFoundBeforeUpdate($id, $cat_ids, $start_date, $end_date)
    {
        $holder = [];
        $offers = Offer::whereNull('deleted_at')
            ->where('team_id', auth()->user()->current_team_id)
            ->where('id', '!=', $id)
            ->whereIntersectsStartDate($start_date)
            ->whereIntersectsEndDate($end_date)
            ->get();



        foreach ($offers as $offer) {

            if (count(array_intersect($cat_ids, $offer->categories_ids))) {
                $holder[] = $offer;
            }
        }


        if (count($holder)) {
            if ($holder[0]) {
                if (count(array_intersect($cat_ids, $holder[0]->categories_ids)) > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * Update Offer
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOffer(Request $request)
    {

        $data = (object) $request->get('data');
        $offer = Offer::find($data->id);
        $offer->name = $data->offer_name;
        $offer->discount_type = $data->discount_type;
        $offer->discount_amount = $data->discount_amount;
        $offer->categories = $data->categories;
        $offer->categories_ids = $request->get('categories_ids');
        $offer->days = $data->days;
        $offer->enabled = $data->enable_offer;
        //        $offer->start_date = Carbon::make($data->selectedDate['start']);
        $offer->start_date = date('Y-m-d', strtotime($data->selectedDate['start']));
        //        $offer->end_date = Carbon::make($data->selectedDate['end']);
        $offer->end_date = date('Y-m-d', strtotime($data->selectedDate['end']));

        //        dd($this->checkIfOfferObjectIsFoundBeforeUpdate($offer->id, $offer->categories_ids , $offer->start_date, $offer->end_date));
        //        if (!$this->checkIfModelObjectIsFoundBeforeUpdate($offer->id, 'offer', null,  $offer->start_date, $offer->end_date)) {
        if (!$this->checkIfOfferObjectIsFoundBeforeUpdate($offer->id, $offer->categories_ids, $offer->start_date, $offer->end_date)) {
            if ($offer->save()) {
                return response()->json(['status' => 'offer_updated'], 200);
            } else {
                return response()->json(['status' => 'something_wrong'], 500);
            }
        } else {
            return response()->json(['status' => 'offer_is_found_before']);
        }
    }

    /**
     * Store Special Price
     * @param Request $request
     * @return JsonResponse
     */
    public function storeSpecialPrice(Request $request)
    {

        $data = (object) $request->get('special_price');
        $unit_category = UnitCategory::find($data->unit_category_id);
        $unit_category_default_days_prices = $unit_category->dailyByDayNamePrices();
    
        $missing_days_in_special_prices = [];
        if(count($data->days_prices)){
            foreach($data->days_prices as $key => $price){
                if(is_null($price) || $price == ''){
                    $missing_days_in_special_prices  [$key] =  $unit_category_default_days_prices[$key];
                }
            }
        }

        $merged_prices = array_merge($data->days_prices,$missing_days_in_special_prices);

        $specialPrice = new SpecialPrice();
        $specialPrice->name = $data->name;
        $specialPrice->unit_category_id = $data->unit_category_id;
        $specialPrice->days_prices = $merged_prices;
        $specialPrice->enabled = $data->enable_special_price;
        //        $specialPrice->start_date = Carbon::make($data->selectedDate['start']);
        $specialPrice->start_date = date('Y-m-d', strtotime($data->selectedDate['start']));
        //        $specialPrice->end_date = Carbon::make($data->selectedDate['end']);
        $specialPrice->end_date = date('Y-m-d', strtotime($data->selectedDate['end']));

        if (!$this->checkIfModelObjectIsFoundBeforeCreate('special_price', $specialPrice->unit_category_id,  $specialPrice->start_date, $specialPrice->end_date)) {
            if ($specialPrice->save()) {
// call staah availability
$data = [
    'category' => $data->unit_category_id,
    'start_date' => $specialPrice->start_date,
    'end_date' => $specialPrice->end_date
];
$data = (object) $data;
$this->staahAvailabilitySpecialPrice($data);
                $team = DB::table('teams')->where('id', $specialPrice->team_id)->select('mytravel_hotel_id')->first();
                if ($team->mytravel_hotel_id) {
                    $mytravel = $this->syncMytravelSpecialPrice($data);
                }


                return response()->json(['status' => 'special_price_created'], 201);
            } else {
                return response()->json(['status' => 'something_wrong'], 500);
            }
        } else {
            return response()->json(['status' => 'special_price_is_found_before']);
        }
    }

    /**
     * Update Special Price
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSpecialPrice(Request $request)
    {

        $data = (object) $request->get('special_price');

        $unit_category = UnitCategory::find($data->unit_category_id);
        $unit_category_default_days_prices = $unit_category->dailyByDayNamePrices();
    
        $missing_days_in_special_prices = [];
        if(count($data->days_prices)){
            foreach($data->days_prices as $key => $price){
                if(is_null($price) || $price == ''){
                    $missing_days_in_special_prices  [$key] =  $unit_category_default_days_prices[$key];
                }
            }
        }

        $merged_prices = array_merge($data->days_prices,$missing_days_in_special_prices);

        $specialPrice =  SpecialPrice::find($data->id);
        $old_start_date = $specialPrice->start_date;
        $old_end_date = $specialPrice->end_date;
        $specialPrice->name = $data->name;
        $specialPrice->unit_category_id = $data->unit_category_id;
        $specialPrice->days_prices = $merged_prices;
        $specialPrice->enabled = $data->enable_special_price;
        //        $specialPrice->start_date = Carbon::make($data->selectedDate['start']);
        $specialPrice->start_date = date('Y-m-d', strtotime($data->selectedDate['start']));
        //        $specialPrice->end_date = Carbon::make($data->selectedDate['end']);
        $specialPrice->end_date = date('Y-m-d', strtotime($data->selectedDate['end']));

        if (!$this->checkIfModelObjectIsFoundBeforeUpdate($specialPrice->id, 'special_price', $specialPrice->unit_category_id, $specialPrice->start_date, $specialPrice->end_date)) {
            if ($specialPrice->save()) {
                $data = [
                    'category' => $specialPrice->unit_category_id,
                    'start_date' => $specialPrice->start_date,
                    'end_date' => $specialPrice->end_date
                ];
                $data = (object) $data;
                $team = DB::table('teams')->where('id', $specialPrice->team_id)->select('mytravel_hotel_id')->first();
                if ($team->mytravel_hotel_id) {
                    $update = $this->updateMytravelSpecialPrice($data, $old_start_date, $old_end_date);
                    $mytravel = $this->syncMytravelSpecialPrice($data);
                }

                return response()->json(['status' => 'special_price_updated'], 200);
            } else {
                return response()->json(['status' => 'something_wrong'], 500);
            }
        } else {
            return response()->json(['status' => 'special_price_is_found_before']);
        }
    }




    /**
     * this simple function purpose is to check that there is an offer matches any of the incoming start or end date
     * if a match found we will prevent adding new offer unless it's different period or we will create new offer
     * our target is the first match of offers ( first object ) that may match by the given dates
     * @param $type
     * @param $start_date
     * @param $end_date
     * @return bool
     */

    private function checkIfModelObjectIsFoundBeforeCreate($type, $unit_category_id = null, $start_date, $end_date)
    {


        switch ($type) {
            case 'offer':
                //                $offer = new ModelDatesMinifiedResource(Offer::whereNull('deleted_at')->where('team_id' , auth()->user()->current_team_id)->WhereDoesntHaveIntersection($start_date,$end_date)->first());
                $offer = new ModelDatesMinifiedResource(Offer::whereNull('deleted_at')->where('team_id', auth()->user()->current_team_id)->whereIntersectsStartDate($start_date)->first());
                return $offer->resource != null ? true : false;
                break;

            case 'special_price':
                //                $specialPrice = new ModelDatesMinifiedResource(SpecialPrice::whereNull('deleted_at')->where('team_id' , auth()->user()->current_team_id)->where('unit_category_id' , $unit_category_id)->WhereDoesntHaveIntersection($start_date,$end_date)->first());
                $specialPrice = new ModelDatesMinifiedResource(SpecialPrice::whereNull('deleted_at')->where('team_id', auth()->user()->current_team_id)->where('unit_category_id', $unit_category_id)->whereIntersectsStartDate($start_date)->first());
                return $specialPrice->resource != null ? true : false;
                break;
        }
    }


    /**
     * Check if specific model intersect with the supplied dates before update
     * @param $id
     * @param $type
     * @param $start_date
     * @param $end_date
     * @return bool
     */
    private function checkIfModelObjectIsFoundBeforeUpdate($id, $type, $unit_category_id = null, $start_date, $end_date)
    {

        switch ($type) {
            case 'offer':
                //                $offer = new ModelDatesMinifiedResource(Offer::whereNull('deleted_at')->where('team_id' , auth()->user()->current_team_id)->where('id' , '!=' , $id)->WhereDoesntHaveIntersection($start_date,$end_date)->first());
                $offer = new ModelDatesMinifiedResource(Offer::whereNull('deleted_at')->where('team_id', auth()->user()->current_team_id)->where('id', '!=', $id)->whereIntersectsStartDate($start_date)->whereIntersectsEndDate($end_date)->first());
                return $offer->resource != null ? true : false;
                break;

            case 'special_price':
                //                $specialPrice = new ModelDatesMinifiedResource(SpecialPrice::whereNull('deleted_at')->where('team_id' , auth()->user()->current_team_id)->where('id' , '!=' , $id)->where('unit_category_id' , $unit_category_id)->WhereDoesntHaveIntersection($start_date,$end_date)->first());
                $specialPrice = new ModelDatesMinifiedResource(SpecialPrice::whereNull('deleted_at')->where('team_id', auth()->user()->current_team_id)->where('id', '!=', $id)->where('unit_category_id', $unit_category_id)->whereIntersectsStartDate($start_date)->whereIntersectsEndDate($end_date)->first());
                return $specialPrice->resource != null ? true : false;
                break;
        }
    }

    public function getActivityDetails(Request $request)
    {
        $log = Activity::with(['causer', 'subject'])->find($request->get('id'));
        return response()->json(['log' =>  $log, 'changes' => $log->changes]);
    }

    public function getTerm(Request $request)
    {
        return Term::find($request->get('id'));
    }

    public function guessReservations(Request $request){
        $unit_ids = $request->get('unit_ids');
        $reservations = Reservation::without('unit')->where('date_out',$request->get('date'))
        ->whereNull('checked_out')
        ->whereNotNull('checked_in')
        ->whereStatus('confirmed')
        ->whereNull('deleted_at')
        ->whereIn('unit_id',$unit_ids)
        ->get(['id','unit_id','date_out','number']);

        $data = [];
        if(count($reservations)){
            foreach ($reservations as $reservation) {
                if(in_array($reservation->unit_id,$unit_ids)){
                    $data[] = [
                        'key' => $reservation->unit_id,
                        'value' => [
                            'res_id' => $reservation->id,
                            'unit_id' => $reservation->unit_id,
                            'date_out' => $reservation->date_out,
                            'res_number' => $reservation->number,
                        ]
                        
                    ];
                }
            }
        }
        return response()->json($data);
    }
}
