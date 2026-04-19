<?php
namespace Surelab\Settings\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Unit;
use App\UnitCategory;
use Illuminate\Support\Facades\DB;
use App\CategorySetting;
//Carbon
use Carbon\Carbon;
use App\SpecialPrice;
use App\OtaReservation;
//OverrideUnitCategoryResource
use App\Http\Resources\OverrideNovaResources\OverrideUnitCategoryResource;
use App\Team;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $team_id = $request->input('team_id');
        $lang = $request->input('lang');

        // Get the categories along with the count of rooms available to sync
        $categories = DB::table('unit_categories')
            ->select('unit_categories.*', DB::raw('COUNT(units.id) as rooms_to_sell'))
            ->join('units', 'unit_categories.id', '=', 'units.unit_category_id')
            ->where('unit_categories.team_id', '=', $team_id)
            ->where('unit_categories.deleted_at', null)
            ->groupBy('unit_categories.id')
            ->get();

        // Map the categories to include the localized name, Arabic name, English name, and synced_units
        $categories = $categories->map(function ($category) use ($lang) {
            $names = json_decode($category->name, true);
            $category->name = $names[$lang];
            $category->text = $names['ar'].' - '.$names['en'];

            // Get the count of units for each category
            $units_count = DB::table('units')
                ->where('unit_category_id', $category->id)
                ->where('deleted_at', null)
                ->count();

            $category->units_count = $units_count;
            $category->synced_units = $category->synced_units; // Assuming the `synced_units` value is already set in the DB

            return $category;
        });

        return response()->json($categories);
    }

    public function getUnits(Request $request){

        $category = $request['category_id'];
        $category_id = json_decode($category)->id;
        $team_id = $request['team_id'];

        $unit_category = DB::table('unit_categories')
            ->where('id', $category_id)
            ->where('team_id', $team_id)
            ->where('deleted_at', null)
            ->first();

        $unite_count = DB::table('units')
            ->where('unit_category_id', $category_id)
            ->where('deleted_at', null)
            ->count();

         $syncedUnits = $unit_category->synced_units;

         $response = [
            'units_count' => $unite_count,
            'synced_units' => $syncedUnits
        ];

        return response()->json($response);






    }

    public function save( $request)
    {
        $category_id = $request['category']['id'];
        $category_sunday_price = $request['prices']['sunday_price'];
        $category_monday_price = $request['prices']['monday_price'];
        $category_tuesday_price = $request['prices']['tuesday_price'];
        $category_wednesday_price = $request['prices']['wednesday_price'];
        $category_thursday_price = $request['prices']['thursday_price'];
        $category_friday_price = $request['prices']['friday_price'];
        $category_saturday_price = $request['prices']['saturday_price'];
        $category_virtual_rooms = $request['virtual_rooms'];
        $second_rateplan_operator = $request['second_rateplan']['operator'];
        $second_rateplan_value = $request['second_rateplan']['value'];

        $categorySetting = CategorySetting::where('category_id', $category_id)->first();

        if (!$categorySetting) {
            $categorySetting = new CategorySetting();
        }
        $categorySetting->category_id = $category_id;
        $categorySetting->sunday_price = $category_sunday_price;
        $categorySetting->monday_price = $category_monday_price;
        $categorySetting->tuesday_price = $category_tuesday_price;
        $categorySetting->wednesday_price = $category_wednesday_price;
        $categorySetting->thursday_price = $category_thursday_price;
        $categorySetting->friday_price = $category_friday_price;
        $categorySetting->saturday_price = $category_saturday_price;
        $categorySetting->virtual_rooms = $category_virtual_rooms;
        $categorySetting->second_rateplan_operator = $second_rateplan_operator;
        $categorySetting->second_rateplan_value = $second_rateplan_value;
        $categorySetting->save();

        $unit_category = UnitCategory::where('id', $category_id)->first();
        $unit_category->synced_units = $request['avalible_to_sync'];
        $unit_category->save();


        return response()->json(['status' => 'success']);
    }

    public function get(Request $request)
    {
        $data =json_decode($request['category_id']);
        $category_id = $data->id;
        $categorySetting = CategorySetting::where('category_id', $category_id)->first();
        if (!$categorySetting) {
            return response()->json(['status' => 'error', 'message' => 'Category setting not found']);
        }

        return response()->json(['status' => 'success', 'data' => $categorySetting]);
    }
    public function updateUnits(Request $request)
    {
        $units = $request->input('units');
        $team_id = $request->input('team_id');

        // Iterate through each unit and update the synced_units for its category
        foreach ($units as $unit) {
            $categoryId = $unit['id'];
            $syncedUnits = $unit['synced_units'];

            DB::table('unit_categories')
                ->where('id', $categoryId)
                ->where('team_id', $team_id)
                ->where('deleted_at', null)
                ->update(['synced_units' => $syncedUnits]);
        }

        return response()->json(['message' => 'Units updated successfully']);
    }

    public function getCategories(Request $request)
    {
        $team_id = $request->input('team_id');
        $lang = $request->input('lang');

        // Get the categories with synced_units greater than one along with the count of rooms available to sync
        $categories = DB::table('unit_categories')
            ->where('unit_categories.team_id', '=', $team_id)
            ->where('unit_categories.deleted_at', null)
            ->groupBy('unit_categories.id')
            ->get();

        // Map the categories to include the localized name, Arabic name, and English name
        $categories = $categories->map(function ($category) use ($lang) {
            $names = json_decode($category->name, true);
            $category->name = $names[$lang];
            $category->text = $names['ar'].' - '.$names['en'];
            return $category;
        });

        return response()->json($categories);
    }

    public function staahAvailability(Request $request)
    {
        $team_id = auth()->user()->current_team_id;
    // in teams table if the team have enable_aisosell is true
    $enable_aisosell = Team::where('id', $team_id)->first()->enable_aiosell;
    if($enable_aisosell){
        // call aisosell function
        $this->aiosellAvailability($request);
    }else{
        $prices = [
            'sunday_price' => $request['prices']['sunday_price'],
            'monday_price' => $request['prices']['monday_price'],
            'tuesday_price' => $request['prices']['tuesday_price'],
            'wednesday_price' => $request['prices']['wednesday_price'],
            'thursday_price' => $request['prices']['thursday_price'],
            'friday_price' => $request['prices']['friday_price'],
            'saturday_price' => $request['prices']['saturday_price'],
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
            $day_Name_price = strtolower($dayName) . '_price';
            $price = $prices[$day_Name_price];

            if($price == null){
                if($specialPrice){
                    $price = $specialPrice->days_prices[$dayName];

                     }else{

                    $price = $request['prices'][$day_Name_price];
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





}
//getReservations
public function getReservations(Request $request)
{
    $team_id = $request->query('team_id');
    $search = $request->query('search');
    $dateFrom = $request->query('date_from');
    $dateTo = $request->query('date_to');
    $status = $request->query('status');
    $isPosted = $request->query('is_posted');
    $isOpen = $request->query('is_open', 'true'); // Default to true if not specified
    $sortBy = $request->query('sort_by', 'created_at');

    $query = OtaReservation::where('team_id', $team_id);

    // Apply search filter
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('booking_id', 'LIKE', "%{$search}%")
              ->orWhere('guest', 'LIKE', "%{$search}%");
        });
    }

    // Apply date range filter
    if ($dateFrom && $dateTo) {
        $query->whereBetween('checkin', [$dateFrom, $dateTo]);
    } elseif ($dateFrom) {
        $query->where('checkin', '>=', $dateFrom);
    } elseif ($dateTo) {
        $query->where('checkin', '<=', $dateTo);
    }

    // Apply status filter
    if ($status && $status !== 'all') {
        $query->where('action', $status);
    }

    // Apply is_posted filter
    if ($isPosted && $isPosted !== 'all') {
        $isPostedBool = $isPosted === 'true' ? true : false;
        $query->where('is_posted', $isPostedBool);
    }

    // Apply is_open filter
    if ($isOpen && $isOpen !== 'all') {
        $isOpenBool = $isOpen === 'true' ? true : false;
        $query->where('is_open', $isOpenBool);
    }

    // Apply sorting
    if ($sortBy === 'checkin') {
        $query->orderBy($sortBy, 'asc');
    } else {
        $query->orderBy($sortBy, 'desc');
    }

    $reservations = $query
        ->get()
        ->map(function($reservation) {
            // Ensure all JSON fields are properly formatted
            $reservation->amount = $this->calculateTotalAmount($reservation->rooms);
            $reservation->guest = is_string($reservation->guest) ? $reservation->guest : json_encode($reservation->guest);
            $reservation->rooms = is_string($reservation->rooms) ? $reservation->rooms : json_encode($reservation->rooms);
            $reservation->request = is_string($reservation->request) ? $reservation->request : json_encode($reservation->request);

            return $reservation;
        });

    return response()->json($reservations);
}

private function calculateTotalAmount($rooms)
{
    if (is_string($rooms)) {
        $rooms = json_decode($rooms, true);
    }

    $totalAmount = 0;

    // If rooms is an array, process each room
    if (is_array($rooms)) {
        foreach ($rooms as $room) {
            if (isset($room['prices']) && is_array($room['prices'])) {
                foreach ($room['prices'] as $price) {
                    if (isset($price['sellRate'])) {
                        $totalAmount += $price['sellRate'];
                    }
                }
            }
        }
    }

    return $totalAmount;
}

public function aiosellAvailability(Request $request)
{
    $virtual_room = $request['virtual_room'];

        $this->save($request);
    $unit_category_id = $request['category']['id'];
    // get the unit category where the id is the same as the request
    $unit_category = UnitCategory::where('id', $unit_category_id)->first();
    // dd($unit_category);
    $prices = [
        'sunday_price' => $unit_category->sunday_day_price,
        'monday_price' => $unit_category->monday_day_price,
        'tuesday_price' => $unit_category->tuesday_day_price,
        'wednesday_price' => $unit_category->wednesday_day_price,
        'thursday_price' => $unit_category->thursday_day_price,
        'friday_price' => $unit_category->friday_day_price,
        'saturday_price' => $unit_category->saturday_day_price,
    ];

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
        $rate = [];
        foreach ($roomsData as $date) {

            $from = $date['from'];
            $to = $date['to'];


            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)
            ->pluck('id')->toArray();
            $available_arr = [];
            foreach($all_units as $unit_id){
                $hasIntersectionWorkable = checkIfUnitHasReservationAiosell($unit_id, Carbon::parse($from));
                if(!$hasIntersectionWorkable){
                    array_push($available_arr, $unit_id);
                }



            }
            $available_units = count($available_arr);
            $unit_category_count = $unit_category->synced_units;
            if($available_units >= $unit_category_count){
                $available_units = $unit_category_count;
            }else{
                $available_units = $available_units;
            }

            $data =

                    [
                        "startDate" => $from,
                        "endDate" => $from,
                        "rooms" => [
                            [
                                "available" => $available_units + $virtual_room,
                                "roomCode" => strval($unit_category_id)
                            ]
                        ]
                    ]

            ;

            // push the data to the room array
            array_push($room, $data);


            $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $date['from'])->where('end_date', '>=', $date['from'])->where('enabled', true)->first();
            $from = $date['from'];
            $to = $date['to'];


            $dayName = Carbon::parse($from)->format('l');
            $day_Name_price = strtolower($dayName) . '_price';
            $price = $request['prices'][$day_Name_price];


            if($price == null){
                if($specialPrice){
                    $price = $specialPrice->days_prices[$dayName];

                     }else{
                        //convert it to integer
                        $price = (int)$prices[$day_Name_price];


                           }

                 }
                 $secondRateplan = $request['second_rateplan'] ?? null;

                $operator = $secondRateplan['operator'];
                $value = $secondRateplan['value'];
                // now apply this operation to $price
                if($operator == '+'){
                    $second_price = $price + $value;
                }elseif($operator == '-'){
                    $second_price = $price - $value;
                }elseif($operator == '*'){
                    $second_price = $price * $value;
                }elseif($operator == '/'){
                    $second_price = $price / $value;
                }




                 $price_data = [
                    'startDate' => $from,
                    'endDate' => $from,
                    'rates' => [
                        [
                            'roomCode' => strval($unit_category_id),
                            'rate' => $price,
                            'rateplanCode' => 'def'
                        ],
                        [
                            'roomCode' => strval($unit_category_id),
                            'rate' => $second_price,
                            'rateplanCode' => 'sec']
                    ]


                ];

                array_push($rate, $price_data);



        }



















    $rate_data = [
            "hotelCode" => auth()->user()->current_team_id,
            "updates" => $rate
        ];
        $username = env('AIOSELL_MEDIATOR_API_USERNAME');
        $password = env('AIOSELL_MEDIATOR_API_PASSWORD');

        $url=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/rates';
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
           CURLOPT_POSTFIELDS => json_encode($rate_data),
           CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '.base64_encode($username.':'.$password)
           ),
        ));

        $response1 = curl_exec($curl);

        $data = [
            "hotelCode" => auth()->user()->current_team_id,
            "updates" => $room
        ];


        $URL2=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/data';


        curl_close($curl);


        $curl = curl_init();

        curl_setopt_array($curl, array(
           CURLOPT_URL => $URL2,
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => '',
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 0,
           CURLOPT_FOLLOWLOCATION => true,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => 'POST',
           CURLOPT_POSTFIELDS => json_encode($data),
           CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '.base64_encode($username.':'.$password)
           ),
        ));

        $response2 = curl_exec($curl);

        curl_close($curl);

var_dump($response1 , $response2);





        }























}



?>
