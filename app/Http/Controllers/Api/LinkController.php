<?php

namespace App\Http\Controllers\Api;

use App\Team;
use App\Term;
use App\Unit;
use App\User;
use stdClass;
use App\Source;
use App\Company;
use Vinkla\Hashids\Facades\Hashids;

use App\Setting;
use App\Customer;
use Carbon\Carbon;
use App\Integration;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\SpecialPrice;
use App\UnitCategory;
use App\WebsiteGallery;
use App\WebsiteSetting;
use App\Jobs\LinkImages;
use App\Objects\Invoice;
use Carbon\CarbonPeriod;
use App\InvoiceCreditNote;
use App\Jobs\SyncRoomData;
use App\Jobs\UpdateRoomJob;
use App\ReservationInvoice;
use Illuminate\Http\Request;
use App\Jobs\ProcessRoomImages;
use App\ReservationServiceMapper;
use App\Events\ReservationCreated;
use App\Events\ReservationUpdated;
use Illuminate\Support\Facades\DB;
use App\Events\ShomoosDeleteEscort;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\GroupReservationBalanceMapper;
use App\Events\ShomosReservationUpdated;
use Illuminate\Support\Facades\Validator;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Mail\Customer\AwaitingConfirmationReservationMail;

// use App\Countries;

class LinkController extends Controller
{
    public function link(Request $request, $id)
    {
        $team_id = $id;
        $team = Team::find($team_id);


        if ($team->mytravel_hotel_id != null) {
            return $this->updateHotel($request, $team_id);
        }


        $name = $team->name;

        $country = DB::table('countries')->where('code', $team->country_code)->select('title')->first();;

        $country = $country->title;
        $country = json_decode($country, true);


        $city = DB::table('cities')->where('id', $team->city_id)->select('title')->first();
        if($city != null){

            $city = $city->title;
            $city = json_decode($city, true);
        }


        $slug = $team->slug;
        $settings = WebsiteSetting::where('team_id', $team_id)->first();
        $contact_phone = $settings->contact_phone;
        $logo = $settings->logo;

        $tax = (getVatPercentageForUnit($team_id) === null) ? 0 : getVatPercentageForUnit($team_id);
        $ewa = (getEwaPercentageForUnit($team_id) === null) ? 0 : getEwaPercentageForUnit($team_id);
        if ($tax != 0 && $ewa != 0) {
            $tax = $tax / 100;
            $ewa = $ewa / 100;
        } else {
            $tax = 0;
            $ewa = 0;
        }

        $settings = DB::table('website_settings')
            ->select('about_us_content', 'social_youtube_url', 'contact_address_description', 'contact_map_url')
            ->where('team_id', $team_id)
            ->first();

        $settings = json_decode(json_encode($settings), true);

        $about_us_content = $settings['about_us_content'];
        $about_us_content = json_decode($about_us_content, true);

        $social_youtube_url = $settings['social_youtube_url'];

        $address = $settings['contact_address_description'];
        $address = json_decode($address, true);

        $map_url = $settings['contact_map_url'];

        $checkin = DB::table('settings')->where('team_id', $team_id)->where('key', 'day_start')->select('value')->first();

        $checkout= DB::table('settings')->where('team_id', $team_id)->where('key', 'day_end')->select('value')->first();

        $policy_ar = DB::table('settings')->where('team_id', $team_id)->where('key', 'contract_notes')->select('value')->first();
        $policy_en = DB::table('settings')->where('team_id', $team_id)->where('key', 'contract_en_notes')->select('value')->first();
        // dd($policy_ar,$policy_en);


        $data = [
            'name' => $name,
            'team_id' => $team_id,
            'bussiness_name' => $name,
            'country' => $country['en'] ?? null,
            'email' => $team_id . '@fandaqah.com',
            'phone' => $contact_phone,
            'price' => '100',
            'city' => $city['en'] ?? null,
            'create_user' => 1,
            'update_user' => 1,
            'password' => "fandaqah2024",
            'hotel' => [
                'title_ar' => $team->name,
                'title_en' => $team->en_name,
                'checkin'=>$checkin->value ?? null,
                'checkout'=>$checkout->value ?? null,
                'location_url' => $map_url,
                'slug' =>  $slug,
                'video' => $social_youtube_url,
                'image_id' => $logo,
                'content' => $about_us_content['en'] ?? null,
                'policy_ar' => $policy_ar->value  ?? null,
                'policy_en' => $policy_en->value  ?? null,
                'content_ar' => $about_us_content['ar'] ?? null,
                'address_ar' => $address['ar'] ?? null,
                'address_en' => $address['en'] ?? null,
                'status' => 'publish',
            ],
            'tax' => $tax,
            'ewa' => $ewa,
            'profit_percent' => 0.00
        ];
        // dd(json_encode($address));
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'team_id' => 'required|integer',
            'bussiness_name' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'hotel.address_ar' => 'required|string',
            'hotel.address_en' => 'required|string',
            'hotel.checkin' => 'required|string',
            'hotel.checkout' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'price' => 'required|numeric',
            'create_user' => 'required|integer',
            'update_user' => 'required|integer',
            'password' => 'required|string',
            'hotel.title_ar' => 'required|string',
            'hotel.title_en' => 'required|string',
            'hotel.policy_ar' => 'required|string',
            'hotel.location_url' => 'required|string',
            'hotel.policy_en' => 'required|string',
            'hotel.video' => 'required|url',
            'hotel.image_id' => 'required|string',
            'hotel.content' => 'required|string',
            'hotel.content_ar' => 'required|string',
            'hotel.status' => 'required|string',
            'tax' => 'required|numeric',
            'ewa' => 'required|numeric',
            'profit_percent' => 'required|numeric',
        ], [
            'name.required' => __('The Team name is required.'),
            'team_id.required' => __('The team ID is required.'),
            'bussiness_name.required' => __('The business name is required.'),
            'country.required' => __('The country is required.'),
            'city.required' => __('The city is required.'),
            'hotel.address_ar.required' => __('The Arabic address is required.'),
            'hotel.address_en.required' => __('The English address is required.'),
            'hotel.checkin.required' => __('The check-in time is required.'),
            'hotel.checkout.required' => __('The check-out time is required.'),
            'email.required' => __('The email is required and must be a valid email.'),
            'email.email' => __('The email format is invalid.'),
            'phone.required' => __('The phone number is required.'),
            'price.required' => __('The price is required and must be a number.'),
            'create_user.required' => __('The create user ID is required.'),
            'hotel.location_url.required' => __('The location URL is required.'),
            'update_user.required' => __('The update user ID is required.'),
            'password.required' => __('A password is required.'),
            'hotel.title_ar.required' => __('The Arabic hotel title is required.'),
            'hotel.title_en.required' => __('The English hotel title is required.'),
            'hotel.policy_ar.required' => __('The Arabic policy is required.'),
            'hotel.policy_en.required' => __('The English policy is required.'),
            // 'hotel.video.required' => __('The video URL is required and must be a valid URL.'),
            'hotel.image_id.required' => __('The image ID is required.'),
            'hotel.content.required' => __('The hotel content is required.'),
            'hotel.content_ar.required' => __('The Arabic content is required.'),
            'hotel.status.required' => __('The status is required.'),
            'tax.required' => 'The tax amount is required.',
            'ewa.required' => __('The ewa value is required.'),
            'profit_percent.required' => __('The profit percentage is required and must be a number.'),
        ]);

        // Check for validation failure
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $curl = curl_init();
        $url = env('MYTRAVEL_API_URL') . '/api/create-user';
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
        // dd(json_encode($data));
        curl_close($curl);

        $jsonResponse = $response;
        $responseArray = json_decode($jsonResponse, true);

        if (isset($responseArray['message'])) {

            $team = Team::find($team_id);
            $team->mytravel_hotel_id = $responseArray['hotel_id'];
            $team->save();
            $send = $this->addRoom($request, $team_id);
            return $response;
        } else {

            return $response;
        }
    }

    public function updateHotel($request, $id)
    {
        // dd($request);
        $team_id = $id;
        $team = Team::find($team_id);


        $name = $team->name;

        $country = DB::table('countries')->where('code', $team->country_code)->select('title')->first();;

        $country = $country->title;
        $country = json_decode($country, true);


        $city = DB::table('cities')->where('id', $team->city_id)->select('title')->first();
        if($city != null){

            $city = $city->title;
            $city = json_decode($city, true);
        }


        $slug = $team->slug;
        $settings = WebsiteSetting::where('team_id', $team_id)->first();
        $contact_phone = $settings->contact_phone;
        $logo = $settings->logo;

        $tax = (getVatPercentageForUnit($team_id) === null) ? 0 : getVatPercentageForUnit($team_id);
        $ewa = (getEwaPercentageForUnit($team_id) === null) ? 0 : getEwaPercentageForUnit($team_id);
        if ($tax != 0 && $ewa != 0) {
            $tax = $tax / 100;
            $ewa = $ewa / 100;
        } else {
            $tax = 0;
            $ewa = 0;
        }

        $settings = DB::table('website_settings')
            ->select('about_us_content', 'social_youtube_url', 'contact_address_description', 'contact_map_url')
            ->where('team_id', $team_id)
            ->first();

        $settings = json_decode(json_encode($settings), true);

        $about_us_content = $settings['about_us_content'];
        $about_us_content = json_decode($about_us_content, true);

        $social_youtube_url = $settings['social_youtube_url'];

        $address = $settings['contact_address_description'];
        $address = json_decode($address, true);

        $map_url = $settings['contact_map_url'];

        $checkin = DB::table('settings')->where('team_id', $team_id)->where('key', 'day_start')->select('value')->first();
        $checkout= DB::table('settings')->where('team_id', $team_id)->where('key', 'day_end')->select('value')->first();

        $policy_ar = DB::table('settings')->where('team_id', $team_id)->where('key', 'contract_notes')->select('value')->first();

        $policy_en = DB::table('settings')->where('team_id', $team_id)->where('key', 'contract_en_notes')->select('value')->first();
        // dd($policy_ar->value,$policy_en);
        $data = [
            'name' => $name,
            'team_id' => $team_id,
            'bussiness_name' => $name,
            'country' => $country['en'] ?? null,
            'email' => $team_id . '@fandaqah.com',
            'phone' => $contact_phone,
            'price' => '100',
            'city' => $city['en'] ?? null,
            'create_user' => 1,
            'update_user' => 1,
            'password' => "fandaqah2024",
            'hotel' => [
                'title_ar' => $team->name,
                'title_en' => $team->en_name,
                'checkin'=>$checkin->value ?? null,
                'checkout'=>$checkout->value ?? null,
                'slug' =>  $slug,
                'video' => $social_youtube_url,
                'image_id' => $logo,
                'location_url' => $map_url,
                'content' => $about_us_content['en']?? null,
                'policy_ar' => $policy_ar->value ?? null,
                'policy_en' => $policy_en->value ?? null,
                'content_ar' => $about_us_content['ar']?? null,
                'address_ar' => $address['ar']?? null,
                'address_en' => $address['en']?? null,
                'status' => 'publish',
            ],
            'tax' => $tax,
            'ewa' => $ewa,
            'profit_percent' => 0.00
        ];
        // dd(json_encode($address));
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'team_id' => 'required|integer',
            'bussiness_name' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'hotel.address_ar' => 'required|string',
            'hotel.address_en' => 'required|string',
            'hotel.checkin' => 'required|string',
            'hotel.checkout' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'price' => 'required|numeric',
            'create_user' => 'required|integer',
            'update_user' => 'required|integer',
            'password' => 'required|string',
            'hotel.title_ar' => 'required|string',
            'hotel.title_en' => 'required|string',
            'hotel.location_url' => 'required|string',
            'hotel.policy_ar' => 'required|string',
            'hotel.policy_en' => 'required|string',
            'hotel.video' => 'required|url',
            'hotel.image_id' => 'required|string',
            'hotel.content' => 'required|string',
            'hotel.content_ar' => 'required|string',
            'hotel.status' => 'required|string',
            'tax' => 'required|numeric',
            'ewa' => 'required|numeric',
            'profit_percent' => 'required|numeric',
        ], [
            'name.required' => __('The Team name is required.'),
            'team_id.required' => __('The team ID is required.'),
            'bussiness_name.required' => __('The business name is required.'),
            'country.required' => __('The country is required.'),
            'city.required' => __('The city is required.'),
            'hotel.address_ar.required' => __('The Arabic address is required.'),
            'hotel.address_en.required' => __('The English address is required.'),
            'hotel.checkin.required' => __('The check-in time is required.'),
            'hotel.checkout.required' => __('The check-out time is required.'),
            'email.required' => __('The email is required and must be a valid email.'),
            'email.email' => __('The email format is invalid.'),
            'phone.required' => __('The phone number is required.'),
            'price.required' => __('The price is required and must be a number.'),
            'create_user.required' => __('The create user ID is required.'),
            'update_user.required' => __('The update user ID is required.'),
            'password.required' => __('A password is required.'),
            'hotel.title_ar.required' => __('The Arabic hotel title is required.'),
            'hotel.title_en.required' => __('The English hotel title is required.'),
            'hotel.policy_ar.required' => __('The Arabic policy is required.'),
            'hotel.policy_en.required' => __('The English policy is required.'),
            'hotel.location_url.required' => __('The location URL is required.'),
            // 'hotel.video.required' => __('The video URL is required and must be a valid URL.'),
            'hotel.image_id.required' => __('The image ID is required.'),
            'hotel.content.required' => __('The hotel content is required.'),
            'hotel.content_ar.required' => __('The Arabic content is required.'),
            'hotel.status.required' => __('The status is required.'),
            'tax.required' => 'The tax amount is required.',
            'ewa.required' => __('The ewa value is required.'),
            'profit_percent.required' => __('The profit percentage is required and must be a number.'),
        ]);

        // Check for validation failure
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $curl = curl_init();

        $mytravel_hotel_id = $team->mytravel_hotel_id;

        $url = env('MYTRAVEL_API_URL') . '/api/hotel-update';
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
        // return $response;
        curl_close($curl);

        $this->addRoom($request, $team_id);
    }

    // public function syncHotelPriceMarkup(Request $request)
    // {
    //     //from table teams where the team_id = $request->team_id
    //     $team = Team::find($request->team_id);
    //     $team->mytravel_markup_price = $request->markup_price;
    //     $team->save();

    //     //add in table settings where the team_id = $request->team_id
    //     $setting = Setting::where('team_id', $request->team_id)->where('key', 'mytravel_markup_price')->first();
    //     if ($setting) {

    //         $markup_price = number_format($request->markup_price, 2, '.', '');
    //         $setting->value = $markup_price;
    //         $setting->save();
    //     } else {

    //         $setting = new Setting();
    //         $setting->team_id = $request->team_id;
    //         $setting->key = 'mytravel_markup_price';
    //         $markup_price = number_format($request->markup_price, 2, '.', '');
    //         $setting->value = $markup_price;
    //         $setting->save();
    //     }

    //     $setting = Setting::where('team_id', $request->team_id)->where('key', 'mytravel_commission')->first();
    //     if ($setting) {

    //         $commission = number_format($request->commission, 2, '.', '');
    //         $setting->value = $commission;
    //         $setting->save();
    //     } else {

    //         $setting = new Setting();
    //         $setting->team_id = $request->team_id;
    //         $setting->key = 'mytravel_commission';
    //         $commission = number_format($request->commission, 2, '.', '');
    //         $setting->value = $commission;
    //         $setting->save();
    //     }

    //     return response()->json(['message' => 'Markup price updated successfully', 'team' => $team], 200);
    // }

    public function addRoom(Request $request, $id)
    {

        $team_id = $id;
        $mytravel_id = Team::find($team_id);
        $mytravel_hotel_id = $mytravel_id->mytravel_hotel_id;

        $categories = UnitCategory::withCount('units')
            ->where('team_id', $team_id)
            ->where('status', 1)
            ->get(['id', 'name', 'team_id', 'description', 'youtube_link']);
        foreach ($categories as $category) {

            $inventory_data = [];
            $start = Carbon::now()->format('Y-m-d');
            $end = Carbon::now()->addDay()->format('Y-m-d');

            for ($i = 0; $i < 90; $i++) {
                $dayName = Carbon::parse($start)->format('l');
                $day_Name_price = strtolower($dayName) . '_day_price';
                $specialPrice = SpecialPrice::where('unit_category_id', $category->id)->where('start_date', '<=', $start)->where('end_date', '>=', $start)->where('enabled', true)->first();
                if($specialPrice){

                    $inventory_price = $specialPrice->days_prices[$dayName];
                }else{

                    $inventory_price = $category[$day_Name_price];
                }
                $ewa_percantage = getEwaPercentageForUnit($team_id);
                $tax_percentage = getVatPercentageForUnit($team_id);
                $ewa_total = getEwaTotalForUnit($inventory_price, $ewa_percantage);
                $tax_total = getVatTotalForUnit($inventory_price, $ewa_total, $tax_percentage);
                $markup_amount = ($inventory_price) * ($mytravel_id->mytravel_markup_price / 100);
                $total_price = $inventory_price + $ewa_total + $tax_total + $markup_amount;
                $all_units = Unit::where('unit_category_id', $category->id)
                    ->where('status', '!=', 3)
                    ->where('enabled', 1)
                    ->where('deleted_at', null)->pluck('id')->toArray();
                $available_arr = [];
                foreach ($all_units as $unit_id) {
                    $hasIntersectionWorkable = checkIfUnitHasReservation($unit_id, Carbon::parse($start));
                    if (!$hasIntersectionWorkable) {
                        array_push($available_arr, $unit_id);
                    }
                }
                $available_units = count($available_arr);

                $inventory_data[] = [
                    'date_start' => $start,
                    'date_end' => $end,
                    'price' => $total_price,
                    'available_units' => $available_units,
                ];
                $start = $end;
                $end = Carbon::parse($end)->addDay()->format('Y-m-d');
            }

            $price =  $category->sunday_day_price;
            $ewa_percantage = getEwaPercentageForUnit($team_id) ? getEwaPercentageForUnit($team_id) : 0;
            $tax_percentage = getVatPercentageForUnit($team_id) ? getVatPercentageForUnit($team_id) : 0;
            $ewa_total = getEwaTotalForUnit($price, $ewa_percantage);
            $tax_total = getVatTotalForUnit($price, $ewa_total, $tax_percentage);
            $total_price = $price + $ewa_total + $tax_total;
            $count = Unit::where('unit_category_id', $category->id)->where('status','!=',3)->where('enabled',1)->count();
            $unit = DB::table('unit_categories')->where('id', $category->id)->first();
            $unit_name =  json_decode($unit->name, true);
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/update';
            $key = env('MY_TRAVEL_KEY');
            $jobData = [

                'title' => $unit_name['en'],
                'title_ar' => $unit_name['ar'],
                'number' => $count,
                'content' => $category->description,
                'video' => $category->youtube_link,
                'parent_id' => $mytravel_hotel_id,
                'price' => $total_price,
                'status' => $category->status,
                'category_id' => $category->id,
                "beds" => $category->number_of_beds,
                "unit_size" => $category->unit_size,
                "adults" => $category->number_of_adults,
                "children" => $category->number_of_children,
                "create_user" => $team_id,
                "inventory" => $inventory_data
            ];

            // Dispatch the job
            SyncRoomData::dispatch($jobData, $url, $key);
        }

        return response()->json(['message' => 'Synced successfully'], 201);
    }


    // public function updateRoom($mytravel_hotel_id, $team_id, $categories)
    // {
    //     $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/update';
    //     $key = env('MY_TRAVEL_KEY');
    //     Bus::dispatch(new UpdateRoomJob($mytravel_hotel_id, $team_id, $categories, $key, $url));

    //     return response()->json(['message' => 'Synced successfully'], 201);
    // }

    public function getAvailabilityForBooking($cat_id, $start, $end, $team_id)
    {

        $team = Team::find($team_id);
        $units = DB::table('units as u')
            ->select('u.id as uid', 'u.name as uname')
            ->whereNull('u.deleted_at')
            ->where('u.unit_category_id', $cat_id)
            ->where('u.team_id', $team_id)
            ->where('u.status', '=', 1)
            ->get();

        $units_count = count($units);

        $period = CarbonPeriod::create($start, $end);

        $result = [];
        $days_list = [];

        if ($units) {
            foreach ($units as $unit) {
                $days = [];
                $i = 1;
                foreach ($period as $key => $date) {
                    $days_list[$key]['name'] = __($date->format('l'));
                    $days_list[$key]['number'] = $date->format('Y-m-d');
                    $days_list[$key]['is_today'] = $date->isToday();
                    $reservations =  DB::table('reservations as r')
                        ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
                        ->leftJoin('highlights as h', 'c.highlight_id', '=', 'h.id')
                        ->select(
                            'r.id as rid',
                            'r.date_in as rdi',
                            'r.date_out as rdo'
                        )
                        ->whereRaw('? between r.date_in and r.date_out', [$date->format('Y-m-d')])
                        ->where('r.date_out', '!=', $date->format('Y-m-d'))
                        ->whereNull('r.checked_out')
                        ->whereNull('r.deleted_at')
                        ->whereIn('r.status', ['confirmed', $team->payment_preprocessor == 'sure-bills' ? 'awaiting-payment' : 'awaiting-confirmation'])
                        ->where('r.team_id', $team_id)
                        ->where('r.unit_id', $unit->uid)
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

        $reservationsCounter = [];
        foreach ($result as $item) {
            $item = (object) $item;
            foreach ($item->days as $obj) {
                $obj = (object) $obj;
                $reservationsCounter[$obj->number][]  = $obj->reservations;
            }
        }

        $notAvailabileDates = [];
        $availability = [];
        foreach ($reservationsCounter as $key => $value) {

            if (array_sum($value) >= $units_count) {
                $notAvailabileDates[] = Carbon::parse($key)->format('Y-m-d');
            }
        }

        return $notAvailabileDates;
    }
    public function formDay($date, $reservations, $unit)
    {
        $day['is_today'] = $date->isToday();
        $day['number'] = $date->format('Y-m-d');
        $day['reservations'] = count($reservations);
        return $day;
    }
    public function getReservationMyTravel(Request $request, $commingresevation = null)
    {

        $aftertax = $request['total_price'];
        $teamid = $request['team_id'];
        // get from tax and ewa from team settings
        $tax = (getVatPercentageForUnit($teamid) === null) ? 0 : getVatPercentageForUnit($teamid);
        $ewa = (getEwaPercentageForUnit($teamid) === null) ? 0 : getEwaPercentageForUnit($teamid);
        $tax = $tax / 100;
        $ewa = $ewa / 100;
        $total_withot_vat = $aftertax / ($tax + 1);
        $total__without_ewa = $total_withot_vat / ($ewa + 1);
        $total_vat = $aftertax - $total_withot_vat;
        $total_ewa = $total_withot_vat - $total__without_ewa;
        $subtotal = $aftertax - $total_vat - $total_ewa;
        $total_price = $aftertax;
        $total_price_raw = $aftertax - $total_vat - $total_ewa;
        $total_tourism = 0;
        $vat_parentage = $tax * 100;
        $team_id = $request['team_id'];
        $phone = intval($request['phone']);
        $name = $request['first_name'] . ' ' . $request['last_name'];
        $email = $request['email'];
        $address = $request['address'];
        $date_in = $request['check_in'];
        $date_out = $request['check_out'];
        $source = "my_travel";
        $matchThese = array('team_id' => $team_id, 'phone' => $phone);
        $customer = Customer::updateOrCreate(
            $matchThese,
            ['team_id' => $team_id,  'name' => $name,  'email' => $email,  'phone' => $phone,  'address' => $address]
        );
        $unitCategory = $request['category_id'];

        $units = UnitCategory::find($unitCategory)->units;
        if ($units) {
            $notAvailableDates = $this->getAvailabilityForBooking($unitCategory, $date_in, $date_out, $team_id);
            if (!in_array(Carbon::parse($request['check_in'])->format('Y-m-d'), $notAvailableDates)) {
                foreach ($units as $unit) {
                    // $hasIntersectionWorkable = $unit->has_intersection_check(Carbon::parse($request->get('check_in')) , Carbon::parse($request->get('check_out')));
                    $hasIntersectionWorkable = checkIfUnitHasReservation($unit->id, Carbon::parse($date_in));
                    if (!$hasIntersectionWorkable) {
                        $source = Source::query()->where('name->en', $source)->where('team_id', $team_id)->first();
                        if (!$source) {
                            $source =  Source::create(['name' => ['en' => $source, 'ar' =>  $source], 'team_id' =>  $team_id, 'deleteable' => 0]);
                        }
                        $team = Team::with('owner')->find($team_id);
                        if ($commingresevation != null) {
                            $reservation = $commingresevation;
                            $reservation->id = $commingresevation->id;
                        } else {

                            $reservation = new Reservation();
                        }
                        $reservation->team_id = $team_id;
                        $reservation->unit_id = $unit->id;
                        $reservation->source_id =  $source->id;
                        $reservation->rent_type = 1;
                        $reservation->customer_id = $customer->id;
                        $reservation->date_in = Carbon::parse($date_in)->format('Y-m-d');
                        $reservation->date_out = Carbon::parse($date_out)->format('Y-m-d');
                        $reservation->is_online = 1;
                        $reservation->status = 'awaiting-payment';
                        $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
                        $reservation->total_price = $total_price;
                        $reservation->sub_total = $subtotal;
                        $reservation->vat_total = $total_vat;
                        $reservation->ewa_total = $total_ewa;
                        $reservation->ttx_total = $total_vat;
                        $reservation->reservation_type = 'group';
                        $reservation->purpose_of_visit = '';
                        $reservation->change_rate = 0;
                        $day_start_time =  $team->day_start() ? $team->day_start() : "13:00";
                        $date_in = Carbon::parse($reservation->date_in)->toDateString();
                        $day_end_time =  $team->day_start() ? $team->day_start() : "16:00";
                        $date_out = Carbon::parse($reservation->date_out)->toDateString();
                        $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$date_in $day_start_time"));
                        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$date_out $day_end_time"));

                        $reservation->date_in_time = $combinedDateInTime;
                        $reservation->date_out_time = $combinedDateOutTime;
                        if ($reservation->save()) {
                            $unit = $reservation->unit;
                            $reservation->prices = $unit->getDatesFromRange(new Carbon($reservation->date_in), new Carbon($reservation->date_out), 1);
                            $reservation->old_prices = [
                                'prices' => $unit->prices(),
                                'min_prices' => $unit->minPrices(),
                                'tourism_percentage'    =>  $unit->getTourismTax(),
                                'vat_parentage'    =>  $unit->getVat(),
                                'ewa_parentage'    =>  $unit->getEwa(),
                            ];

                            $reservation->forceWithdrawFloat($reservation->total_price, [
                                'category' => 'reservation',
                                'statement' => 'Reservation Total Price',
                            ], true, false);


                            $reservation->wallet->save();

                            $reservation->wallet->refreshBalance();

                            $reservation->save();

                            // Send awaiting confirmation email to customer from website
                            if ($reservation->customer && $reservation->customer->email) {
                                // Mail::to($reservation->customer->email)->send(new AwaitingConfirmationReservationMail($reservation));
                            }

                            $response['reservation'] = $reservation;


                            event(new ReservationCreated($reservation, false));


                            return response()->json($response);
                        }
                    }
                }
            }
        }
    }

    public function linkImages($team_id)
    {

        $settings = WebsiteSetting::where('team_id', $team_id)->first();
        $webiste_id = $settings->id;
        $gallery = WebsiteGallery::where('website_id', $webiste_id)->get();

        if ($gallery->isEmpty()) {

            return response()->json(['message' => 'No images found'], 404);
        } else {

            $team = Team::find($team_id);
            $mytravel_hotel_id = $team->mytravel_hotel_id;
            $photo_types = [];
            foreach ($gallery as $photo_type) {
                $images = [
                    'type' => $photo_type->type,
                    'image_path' => $photo_type->path,
                    'hotel_id' => $mytravel_hotel_id,
                    'team_id' => $team_id
                ];
                array_push($photo_types, $images);
            }


            $jobData = [

                'photo_types' => $photo_types,

            ];
            LinkImages::dispatch($jobData);

            return response()->json(['message' => 'Image linking started successfully'], 200);
        }
    }

    public function getHotelImages($team_id)
    {

        $settings = WebsiteSetting::where('team_id', $team_id)->first();
        $webiste_id = $settings->id;
        $gallery = WebsiteGallery::where('website_id', $webiste_id)->get();

        if ($gallery->isEmpty()) {

            return response()->json(['message' => 'No images found'], 404);
        } else {

            $team = Team::find($team_id);
            $mytravel_hotel_id = $team->mytravel_hotel_id;
            $photo_types = [];
            foreach ($gallery as $photo_type) {
                $images = [
                    'type' => $photo_type->type,
                    'image_path' => $photo_type->path,
                    'hotel_id' => $mytravel_hotel_id,
                    'team_id' => $team_id
                ];
                array_push($photo_types, $images);
            }
            return response()->json($photo_types);
        }
    }

    public function getRoomImages($id)
    {
        $unit_category = UnitCategory::find($id);
        $media_main = $unit_category->getMedia("main");
        $media_gallery = $unit_category->getMedia("images");
        // dd($media);
        if ($media_main->isEmpty()) {
            return response()->json(['error' => 'Main Image Not Found'], 404);
        } else {

            $unit_category = UnitCategory::find($id);
            $media_main = $unit_category->getMedia("main");
            $media_gallery = $unit_category->getMedia("images");

            if ($media_main->isEmpty()) {
                return response()->json(['error' => 'Main Image Not Found'], 404);
            }

            $unit_category_image_main_url = $unit_category->getMedia("main")[0]->getUrl();
            $mediaItems = $unit_category->getMedia('images');
            $galleryUrls = [];
            foreach ($mediaItems as $mediaItem) {
                $galleryUrls[] = $mediaItem->getUrl();
            }

            $data = [
                'main_image_url' => $unit_category_image_main_url,
                'gallery' => $galleryUrls,
                'category_id' => $id,
            ];
            // dd($data);
            // Dispatch the job
            ProcessRoomImages::dispatch($data);

            return response()->json(['message' => 'Image processing started successfully'], 200);
        }
    }

    public function getUnitImages($id)
    {
        $unit_category = UnitCategory::find($id);
        $media_main = $unit_category->getMedia("main");
        $media_gallery = $unit_category->getMedia("images");
        // dd($media);
        if ($media_main->isEmpty()) {
            return response()->json(['error' => 'Main Image Not Found'], 404);
        } else {

            $unit_category = UnitCategory::find($id);
            $media_main = $unit_category->getMedia("main");
            $media_gallery = $unit_category->getMedia("images");

            if ($media_main->isEmpty()) {
                return response()->json(['error' => 'Main Image Not Found'], 404);
            }

            $unit_category_image_main_url = $unit_category->getMedia("main")[0]->getUrl();
            $mediaItems = $unit_category->getMedia('images');
            $galleryUrls = [];
            foreach ($mediaItems as $mediaItem) {
                $galleryUrls[] = $mediaItem->getUrl();
            }
            $data = [
                'main_image_url' => $unit_category_image_main_url,
                'gallery' => $galleryUrls,
                'category_id' => $id,
            ];
            return response()->json($data);
        }
    }

    public function createReservations(Request $request)
    {
        $matchThese = array('team_id' => $request->team_id, 'phone' => $request->phone);
        $company = Company::updateOrCreate([
            'email' => $request->get('company_email'),
            'team_id' => $request->get('team_id'),
        ], [
            'name' => "Dyafa Booking Engine",
            'phone' => "+966558439000",
            'city' => "Alkhubar",
            'address' => "Prince Turky Street",
            'person_incharge_name' => "Dyafa Booking Engine",
            'person_incharge_phone' => "+966558439000",
            'email' => "booking@dyafa.com",
            'tax_number' => "511423123300003",
        ]);
        $company_id = $company->id;
        $main_reservation = $request->get('main_reservation');
        $source_en = $request->get('source_en');
        $source_ar = $request->get('source_ar');
        $source_number = $request->get('referance_id');
        $team_id = $request->team_id;

        $source = Source::query()->where('name->en', $source_en)->where('team_id', $team_id)->first();
        if (!$source) {
            $source =  Source::create(['name' => ['en' => $source_en, 'ar' =>  $source_ar], 'team_id' =>  $team_id, 'deleteable' => 0]);
        }
        $date_start = Carbon::parse($request->get('date_start'));
        $date_end = Carbon::parse($request->get('date_end'));
        $rent_type = $request->get('rent_type') && $request->get('rent_type') == 'daily' ? 1 : 2;
        $team_id = $request->team_id;
        $currentPeriodAccordingToSelectedDates = CarbonPeriod::create($date_start, $date_end->subDay());

        $category_ids = $request->get('category_ids');

        $units_selected = [];
        $from = $request->date_start;
        $to = $request->date_end;
        // dd($from, $to);
        $selected_units = [];
        $unavailable_categories = [];
        foreach ($category_ids as $category_id) {
            $available_units = Unit::where('unit_category_id', $category_id)
                ->whereNotIn('id', $selected_units) // Exclude already selected units
                ->whereDoesntHave('reservations', function ($query) use ($from, $to) {
                    $query->where(function ($q) use ($from, $to) {
                        $q->where('date_in', '<=', $to)
                            ->where('date_out', '>=', $from);
                    });
                })->get();

            if ($available_units->isNotEmpty()) {
                // Select the first available unit
                $available_unit = $available_units->first();
                $units_selected[] = ['id' => $available_unit->id];
                $selected_units[] = $available_unit->id; // Add to selected units list
            } else {
                // Handle case where no units are available
                $unavailable_categories[] = $category_id;
            }
        }
        $star_unit_id = $units_selected[0];
        $filtered_units_selected = [];
        for ($i = 1; $i < count($units_selected); $i++) {
            $filtered_units_selected[] = ['id' => $units_selected[$i]['id']];
        }

        $currentDatesHolder = [];
        foreach ($currentPeriodAccordingToSelectedDates as $date) {
            if (!in_array($date->format('Y-m-d'), $currentDatesHolder)) {
                $currentDatesHolder[] = $date->format('Y-m-d');
            }
        }

        /**
         * the first scenario the user did not select a main reservation and by default a highlighted
         */
        if ($star_unit_id) {
            // i will create the main reservation on this star unit it , then proceeed attaching other reservations to it
            $counter = TeamCounter::where('team_id', $team_id)->first();
            if (!$counter) {
                $counter = TeamCounter::create();
                $counter->forceFill([
                    'team_id' => $team_id,
                ])->save();
            }
            $reservation_number = $counter->reservation_num;
            $counter->last_reservation_number =  $counter->reservation_num;
            $counter->save();

            if (count($units_selected) > 1) {
                // this means user selected many units , so we will create main reservation , the attach other reservations to it
                // getting main unit the will hold the main reservation
                $unit = Unit::where('id', $star_unit_id)->first();
                // dd($unit->enabled);
                $actual_incoming_date_out =  $date_end->addDay();
                if ($unit->enabled != 1 && ($unit->status != 1 || $unit->status != 2)) {
                    // if the unit is not enabled or not available ( in maintenance or cleaning)
                    return response()->json(['status' => false, 'message' => 'Unfortunately during the booking process , the unit became un-available to book', 'unit' => $unit]);
                }
                // check in the real time if the unit still available
                $unitHasReservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit, $date_start, $currentDatesHolder);
                if (!$unitHasReservation) {
                    $calculations = $unit->getDatesFromRange($date_start, $actual_incoming_date_out, $rent_type);
                    $startDate = Carbon::parse($date_start);
                    $endDate = Carbon::parse($actual_incoming_date_out);

                    // Initialize total calculations
                    $totalPrice = 0;
                    $totalSubTotal = 0;
                    $totalEwa = 0;
                    $totalVat = 0;
                    $totalPriceRaw = 0;
                    $specials = [];

                    $startDate = Carbon::parse($date_start);
                    $endDate = Carbon::parse($actual_incoming_date_out);
                    // Calculate the number of nights
                    $numberOfNights = $endDate->diffInDays($startDate);

                    // Iterate over the number of nights
                    for ($i = 0; $i < $numberOfNights + 1; $i++) {
                        $dayName = $startDate->format('l');

                        // Fetch special price for the current day
                        $specialPrice = SpecialPrice::where('unit_category_id', $unit->unit_category_id)
                                                    ->where('start_date', '<=', $startDate)
                                                    ->where('end_date', '>=', $startDate)
                                                    ->where('enabled', true)
                                                    ->first();
                        if ($specialPrice) {
                            // Check if there's a price for the current day
                            if (!is_null($specialPrice->days_prices[$dayName])) {
                                $dayPrice = $specialPrice->days_prices[$dayName];
                                // Calculate prices for the current day
                                $subTotal = $dayPrice;
                                $Ewa = $dayPrice * ($unit->getEwa() / 100);
                                $Vat = ($dayPrice + $Ewa) * ($unit->getVat() / 100);
                                $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                                // Add to the total calculations
                                $totalPrice += $totalPriceRaw;
                                $totalSubTotal += $subTotal;
                                $totalEwa += $Ewa;
                                $totalVat += $Vat;

                                $specials[] = [
                                    'date' => $startDate->format('Y-m-d'),
                                    'price' => $dayPrice,

                                ];
                            }else{
                                $unit_category = UnitCategory::where('id', $unit->unit_category_id)->first();
                                $day_Name_price = strtolower($dayName) . '_day_price';
                                $dayPrice = $unit_category->$day_Name_price;
                                // Calculate prices for the current day
                                $subTotal = $dayPrice;
                                $Ewa = $dayPrice * ($unit->getEwa() / 100);
                                $Vat = ($dayPrice + $Ewa) * ($unit->getVat() / 100);
                                $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                                // Add to the total calculations
                                $totalPrice += $totalPriceRaw;
                                $totalSubTotal += $subTotal;
                                $totalEwa += $Ewa;
                                $totalVat += $Vat;
                            }
                        }else{
                                $calculations = $unit->getDatesFromRange($date_start, $date_end, $rent_type);
                                $unit_category = UnitCategory::where('id', $unit->unit_category_id)->first();
                                $day_Name_price = strtolower($dayName) . '_day_price';
                                $dayPrice = $unit_category->$day_Name_price;
                                // Calculate prices for the current day
                                $subTotal = $dayPrice;
                                $Ewa = $dayPrice * ($unit->getEwa() / 100);
                                $Vat = ($dayPrice + $Ewa) * ($unit->getVat() / 100);

                                $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                                // Add to the total calculations
                                $totalPrice += $totalPriceRaw;
                                $totalSubTotal += $subTotal;
                                $totalEwa += $Ewa;
                                $totalVat += $Vat;
                        }

                        // Move to the next day
                        $startDate->addDay();
                    }
                    $transformedArray = [];
                    foreach ($specials as $item) {
                        $transformedArray[] = [
                            'date' => $item['date'],
                            'specialPrice' => $item['price']
                        ];
                    }

                    $special_prices = json_encode($transformedArray);
                    $calculations['price'] = $totalPrice;
                    $calculations['sub_total'] = $totalSubTotal;
                    $calculations['total_ewa'] = $totalEwa;
                    $calculations['total_vat'] = $totalVat;
                    $calculations['total_price_raw'] = $totalPrice;
                    $calculations['total_price'] = $totalPrice;
                    $calculations['min_sub_total'] = $totalSubTotal;


                    $actual_incoming_date_out =  $date_end->addDay();
                    $after = $actual_incoming_date_out->format('Y-m-d');
                    $customer = Customer::updateOrCreate(
                        $matchThese,
                        ['team_id' => $team_id,  'name' => $request->customer['name'],  'email' => $request->customer['email'],  'phone' => $request->customer['phone'],  'address' => $request->customer['address']]
                    );
                    $mainReservationBlueprint = new Reservation();
                    $mainReservationBlueprint->team_id = $team_id;
                    $mainReservationBlueprint->customer_id = $customer->id;
                    $mainReservationBlueprint->unit_id = $unit->id;
                    $mainReservationBlueprint->number = $reservation_number;
                    $mainReservationBlueprint->source_id = $source->id;
                    $mainReservationBlueprint->source_num = $source_number;
                    $mainReservationBlueprint->special_prices = $special_prices;
                    $mainReservationBlueprint->rent_type = $rent_type;
                    $mainReservationBlueprint->date_in = $date_start->format('Y-m-d');
                    $mainReservationBlueprint->date_out = $after;
                    // attaching creator id for the reservation
                    $mainReservationBlueprint->created_by = $team_id;
                    $mainReservationBlueprint->total_price = $calculations['total_price_raw'];
                    $mainReservationBlueprint->sub_total = $calculations['sub_total'];
                    $mainReservationBlueprint->vat_total = $calculations['total_vat'];
                    $mainReservationBlueprint->ewa_total = $calculations['total_ewa'];
                    $mainReservationBlueprint->ttx_total = $request['total_ttx'];
                    $mainReservationBlueprint->change_rate = 0;

                    $day_start_time =  \App\Handlers\Settings::get('day_start');
                    $day_end_time =  \App\Handlers\Settings::get('day_end');
                    $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$mainReservationBlueprint->date_in $day_start_time"));
                    $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$mainReservationBlueprint->date_out $day_end_time"));
                    $mainReservationBlueprint->date_in_time = $combinedDateInTime;
                    $mainReservationBlueprint->date_out_time = $combinedDateOutTime;

                    $mainReservationBlueprint->reservation_type = 'group';
                    $mainReservationBlueprint->company_id = $company_id;

                    $mainReservationBlueprint->old_prices = [
                        'prices' => $unit->prices(),
                        'min_prices' => $unit->minPrices(),
                        'tourism_percentage'    =>  $unit->getTourismTax(),
                        'vat_parentage'    =>  $unit->getVat(),
                        'ewa_parentage'    =>  $unit->getEwa(),
                    ];
                    if ($specialPrice){
                        foreach ($calculations['days'] as &$day) {
                            $day['price_row'] = $calculations['sub_total'];
                            $day['price'] = $calculations['sub_total'];
                        }
                        unset($day);
                        $mainReservationBlueprint->prices = $calculations;
                    }else{
                        $mainReservationBlueprint->prices = $unit->getDatesFromRange($date_start, $date_end, 1);

                    // dd($date_start, $mainReservationBlueprint->date_out);

                    }
                    try {
                        if ($mainReservationBlueprint->save()) {

                            if (count($request->get('reservation_services_selected'))) {
                                foreach ($request->get('reservation_services_selected') as $reservation_service) {
                                    $reservationServiceMapper = new ReservationServiceMapper();
                                    $reservationServiceMapper->reservation_id = $mainReservationBlueprint->id;
                                    $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                                    $reservationServiceMapper->save();
                                }
                            }
                            try {
                                $mainReservationBlueprint->forceWithdrawFloat($mainReservationBlueprint->total_price, [
                                    'category' => 'reservation',
                                    'statement' => 'Reservation Total Price',
                                ], true, false);

                                // get the balance for this singular
                                $balance = $mainReservationBlueprint->wallet->decimal_places == 3 ? $mainReservationBlueprint->balance / 1000 : $mainReservationBlueprint->balance / 100;
                                // map the balance in the the mapper table
                                GroupReservationBalanceMapper::updateOrCreate(
                                    ['reservation_id' => $mainReservationBlueprint->id],
                                    ['balance' => floatval($balance)]
                                );

                                /**
                                 * After creating the main reservation , we need to attach other reservations to it
                                 */
                                $reservation_ids = [];
                                $reservation_ids[] = $mainReservationBlueprint->id;
                                foreach ($filtered_units_selected as $unit_obj) {

                                    // getting the attachables
                                    $attachables = Reservation::where('attachable_id', $mainReservationBlueprint->id)
                                        ->withTrashed()
                                        ->whereIn('status', ['confirmed', 'canceled'])
                                        ->orderBy('id', 'asc')
                                        ->get();

                                    if (!count($attachables)) {
                                        // it means it is the first attach
                                        $reservation_number =  'A' . $mainReservationBlueprint->number;
                                    } else {
                                        $last_key = 0;
                                        $limit = "AZZZ";
                                        $alphabetArr = [];
                                        for ($x = "A", $limit++; $x != $limit; $x++) {
                                            $alphabetArr[] = $x;
                                        }

                                        $last_child_reservation = collect($attachables)->sortByDesc('id')->first();
                                        $last_child_reservation_alpha = explode($mainReservationBlueprint->number, $last_child_reservation->number)[0];
                                        $last_child_reservation_alpha_index = array_search($last_child_reservation_alpha, $alphabetArr);
                                        $next_child_resservation_alpha = $alphabetArr[$last_child_reservation_alpha_index + 1];
                                        $reservation_number =  $next_child_resservation_alpha . $mainReservationBlueprint->number;
                                    }

                                    $unit = Unit::find($unit_obj['id']);

                                    if ($unit->enabled != 1 && ($unit->status != 1 || $unit->status != 2)) {
                                        // if the unit is not enabled or not available ( in maintenance or cleaning)
                                        return response()->json(['status' => false, 'message' => 'Unfortunately during the booking process , the unit became un-available to book']);
                                    }
                                    // check in the real time if the unit still available
                                    $unitHasReservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit, $date_start, $currentDatesHolder);
                                    if (!$unitHasReservation) {
                                        // i had to do this step cause we are subtracting in unit model and i had to subtract it in the begining of the method
                                        // $date_end = $date_end->addDay();
                                        // we will proceed
                                        $calculations = $unit->getDatesFromRange($date_start, $date_end,$rent_type);

                                        $startDate = Carbon::parse($date_start);
                                        $endDate = Carbon::parse($date_end);
                                        // Initialize total calculations
                                        $totalPrice = 0;
                                        $totalSubTotal = 0;
                                        $totalEwa = 0;
                                        $totalVat = 0;
                                        $totalPriceRaw = 0;
                                        $specials = [];

                                        for ($i = 0; $i < $numberOfNights + 1; $i++) {
                                            $dayName = $startDate->format('l');

                                            // Fetch special price for the current day
                                            $specialPrice = SpecialPrice::where('unit_category_id', $unit->unit_category_id)
                                                                        ->where('start_date', '<=', $startDate)
                                                                        ->where('end_date', '>=', $startDate)
                                                                        ->where('enabled', true)
                                                                        ->first();
                                            if ($specialPrice) {
                                                // Check if there's a price for the current day
                                                if (!is_null($specialPrice->days_prices[$dayName])) {
                                                    $dayPrice = $specialPrice->days_prices[$dayName];
                                                    // Calculate prices for the current day
                                                    $subTotal = $dayPrice;
                                                    $Ewa = $dayPrice * ($unit->getEwa() / 100);
                                                    $Vat = ($dayPrice + $Ewa) * ($unit->getVat() / 100);
                                                    $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                                                    // Add to the total calculations
                                                    $totalPrice += $totalPriceRaw;
                                                    $totalSubTotal += $subTotal;
                                                    $totalEwa += $Ewa;
                                                    $totalVat += $Vat;

                                                    $specials[] = [
                                                        'date' => $startDate->format('Y-m-d'),
                                                        'price' => $dayPrice,

                                                    ];
                                                }else{
                                                    $unit_category = UnitCategory::where('id', $unit->unit_category_id)->first();
                                                    $day_Name_price = strtolower($dayName) . '_day_price';
                                                    $dayPrice = $unit_category->$day_Name_price;
                                                    // Calculate prices for the current day
                                                    $subTotal = $dayPrice;
                                                    $Ewa = $dayPrice * ($unit->getEwa() / 100);
                                                    $Vat = ($dayPrice + $Ewa) * ($unit->getVat() / 100);
                                                    $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                                                    // Add to the total calculations
                                                    $totalPrice += $totalPriceRaw;
                                                    $totalSubTotal += $subTotal;
                                                    $totalEwa += $Ewa;
                                                    $totalVat += $Vat;
                                                }
                                            }else{
                                                    $unit_category = UnitCategory::where('id', $unit->unit_category_id)->first();
                                                    $day_Name_price = strtolower($dayName) . '_day_price';
                                                    $dayPrice = $unit_category->$day_Name_price;
                                                    // Calculate prices for the current day
                                                    $subTotal = $dayPrice;
                                                    $Ewa = $dayPrice * ($unit->getEwa() / 100);
                                                    $Vat = ($dayPrice + $Ewa) * ($unit->getVat() / 100);

                                                    $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                                                    // Add to the total calculations
                                                    $totalPrice += $totalPriceRaw;
                                                    $totalSubTotal += $subTotal;
                                                    $totalEwa += $Ewa;
                                                    $totalVat += $Vat;
                                            }

                                            // Move to the next day
                                            $startDate->addDay();
                                        }
                                        $transformedArray = [];
                                        foreach ($specials as $item) {
                                            $transformedArray[] = [
                                                'date' => $item['date'],
                                                'specialPrice' => $item['price']
                                            ];
                                        }

                                        $special_prices = json_encode($transformedArray);
                                        $calculations['price'] = $totalPrice;
                                        $calculations['sub_total'] = $totalSubTotal;
                                        $calculations['total_ewa'] = $totalEwa;
                                        $calculations['total_vat'] = $totalVat;
                                        $calculations['total_price_raw'] = $totalPrice;
                                        $calculations['total_price'] = $totalPrice;
                                        $calculations['min_sub_total'] = $totalSubTotal;

                                        $reservation = new Reservation();
                                        $reservation->team_id = $team_id;
                                        $reservation->unit_id = $unit->id;
                                        $reservation->number = $reservation_number;
                                        $reservation->source_id = $source->id;
                                        $reservation->source_num = $source_number;
                                        $reservation->rent_type = $rent_type;
                                        $reservation->date_in = $date_start->format('Y-m-d');
                                        $reservation->date_out = $date_end->addDay()->format('Y-m-d');
                                        $reservation->special_prices = $special_prices;
                                        // attaching creator id for the reservation
                                        $reservation->created_by = $team_id;
                                        $reservation->total_price = $calculations['total_price_raw'];
                                        $reservation->sub_total = $calculations['sub_total'];
                                        $reservation->vat_total = $calculations['total_vat'];
                                        $reservation->ewa_total = $calculations['total_ewa'];
                                        $reservation->ttx_total = $request['total_ttx'];
                                        $reservation->change_rate = 0;

                                        $day_start_time =  \App\Handlers\Settings::get('day_start');
                                        $day_end_time =  \App\Handlers\Settings::get('day_end');
                                        $combinedDateInTime = date('Y-m-d H:i:s', strtotime("$reservation->date_in $day_start_time"));
                                        $combinedDateOutTime = date('Y-m-d H:i:s', strtotime("$reservation->date_out $day_end_time"));
                                        $reservation->date_in_time = $combinedDateInTime;
                                        $reservation->date_out_time = $combinedDateOutTime;

                                        $reservation->reservation_type = 'group';
                                        $reservation->company_id = $company_id;
                                        $reservation->customer_id = $customer->id;
                                        $reservation->attachable_id = $mainReservationBlueprint->id;
                                        $reservation->old_prices = [
                                            'prices' => $unit->prices(),
                                            'min_prices' => $unit->minPrices(),
                                            'tourism_percentage'    =>  $unit->getTourismTax(),
                                            'vat_parentage'    =>  $unit->getVat(),
                                            'ewa_parentage'    =>  $unit->getEwa(),
                                        ];
                                        if ($specialPrice){
                                            foreach ($calculations['days'] as &$day) {
                                                $day['price_row'] = $calculations['sub_total'];
                                                $day['price'] = $calculations['sub_total'];
                                            }
                                            unset($day);
                                            $reservation->prices = $calculations;
                                        }else{
                                            $reservation->prices = $unit->getDatesFromRange($date_start, $date_end, 1);



                                        }
                                        try {
                                            if ($reservation->save()) {
                                                $reservation_ids[] = $reservation->id;
                                                if (count($request->get('reservation_services_selected'))) {
                                                    foreach ($request->get('reservation_services_selected') as $reservation_service) {
                                                        $reservationServiceMapper = new ReservationServiceMapper();
                                                        $reservationServiceMapper->reservation_id = $reservation->id;
                                                        $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                                                        $reservationServiceMapper->save();
                                                    }
                                                }
                                                try {
                                                    $reservation->forceWithdrawFloat($reservation->total_price, [
                                                        'category' => 'reservation',
                                                        'statement' => 'Reservation Total Price',
                                                    ], true, false);

                                                    // get the balance for this singular
                                                    $balance = $reservation->wallet->decimal_places == 3 ? $reservation->balance / 1000 : $reservation->balance / 100;
                                                    // map the balance in the the mapper table
                                                    GroupReservationBalanceMapper::updateOrCreate(
                                                        ['reservation_id' => $reservation->id],
                                                        ['balance' => floatval($balance)]
                                                    );

                                                    // return response()->json(['success' => true , 'reservation' => $reservation , 'singular' => true]);
                                                } catch (\Throwable $th) {
                                                    // return response()->json(['success' => false , 'message' => $th->getMessage() , 'stack-trace' => $th->getTrace()]);
                                                }
                                            }
                                        } catch (\Throwable $th) {
                                            return response()->json(['success' => false, 'message' => $th->getMessage()]);
                                        }
                                    } else {

                                        // return response()->json(['success' => false , 'message' => 'Unfortunately during the booking process , the unit has been booked']);
                                    }
                                }
                                foreach ($reservation_ids as $reservation_id) {
                                    $reservation = Reservation::where('id', $reservation_id)->first();
                                    $reservation->status = 'awaiting-payment';
                                    $reservation->save();
                                }
                                return response()->json(['success' => true, 'reservation_ids' => $reservation_ids, 'reservation_number' => $mainReservationBlueprint->number, 'single' => false]);
                            } catch (\Throwable $th) {
                                return response()->json(['success' => false, 'message' => $th->getMessage(), 'stack-trace' => $th->getTrace()]);
                            }
                        }
                    } catch (\Throwable $th) {
                        return response()->json(['success' => false, 'message' => $th->getMessage()]);
                    }
                } else {

                    return response()->json(['success' => false, 'message' => 'Unfortunately during the booking process , some units has been booked']);
                }

                return response()->json($filtered_units_selected);
            }
        }


        return response()->json($request->all());
    }
    public function updateCustomer(Request $request)
    {
        // $model = Reservation::find($request->id);
        $c = $request['customer'];
        $c['phone'] = preg_replace('/\s+/', '', $c['phone']);

        /**
         * Unique validation for customer id number
         * @note : commented out as per request from islam
         */
        if ($c['id_number'] && !$c['id']) {

            $validator = Customer::validate($c, $c['id']);

            if ($validator->fails()) {
                $response = [
                    'success' => false,
                    'message' => 'id_number_taken'
                ];
                return response()->json($response);
            }
        }
        $customer = Customer::updateOrCreate(['id' => $c['id']], $c);
        $reservation = Reservation::findOrFail($request->id);

        if (!$reservation->team->integration_shms && !$reservation->team->integration_shomoos_version_one && ($customer->id != $reservation->customer_id)) {
            // in this case i need to remove all escorts for old customer ( if any )
            // i should detach from customer_guest_reservation table
            $reservation->reservation_guests()->where('reservation_id', $reservation->id)->detach();
            // then i should update the escort reservation_id inside guest table and set it to null
            if (count($reservation->guests)) {
                foreach ($reservation->guests as $escort) {
                    $escort->reservation_id = null;
                    $escort->save();

                    // fire a delete escort job to release this escort from shomos
                    event(new ShomoosDeleteEscort($reservation->id, $escort));
                }
            }
        }

        if (!$request->get('attach_in_all_reservation')) {
            $reservation->update(['customer_id' => $customer->id, 'action_type' => Reservation::ACTION_UPDATERESERVATIONCUSTOMER]);
        } else {
            $main_reservation = null;
            $push_main_reservation_to_collection = false;
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet', 'unit')
                ->where('reservation_type', 'group')
                ->whereDoesntHave('customer')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->whereIn('status', ['confirmed', 'awaiting-payment'])
                ->whereNull('deleted_at')
                ->get();

            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }

            if (count($reservations)) {
                foreach ($reservations as $related_reservation) {
                    if (is_null($related_reservation->customer_id)) {
                        $related_reservation->update(['customer_id' => $customer->id, 'action_type' => Reservation::ACTION_UPDATERESERVATIONCUSTOMER]);
                    }
                }
            }
        }
        if ($request->has('attach_customer') && $request->get('attach_customer')) {
            // then i need to fetch all public withdraw transactions then update the value of received_by inside meta
            $withdraw_transactions = $reservation->getWithdrawTransactions();
            if (count($withdraw_transactions)) {
                foreach ($withdraw_transactions as $transaction) {
                    if (isset($transaction['meta']) && isset($transaction['meta']['received_by'])) {
                        // the below step to avoid indirect modification exception -- note for developers  -_-
                        $metadata = collect($transaction['meta'])->jsonserialize();
                        $metadata['received_by'] = $customer->name;
                        $transaction['meta'] = $metadata;
                        $transaction->save();
                    }
                }
            }

            // if attach_in_all_reservation option found then we will do the magic  ...

        } else {
            // then i need to fetch all public withdraw transactions then update the value of received_by inside meta
            $withdraw_transactions = $reservation->getWithdrawTransactions();
            if (count($withdraw_transactions)) {
                foreach ($withdraw_transactions as $transaction) {
                    if (isset($transaction['meta']) && isset($transaction['meta']['from'])) {
                        // the below step to avoid indirect modification exception -- note for developers  -_-
                        $metadata = collect($transaction['meta'])->jsonserialize();
                        $metadata['from'] = $customer->name;
                        $transaction['meta'] = $metadata;
                        $transaction->save();
                    }
                }
            }

            $deposit_transactions = $reservation->getDepositTransactions();
            if (count($deposit_transactions)) {
                foreach ($deposit_transactions as $transaction) {
                    if (isset($transaction['meta']) && isset($transaction['meta']['from'])) {
                        // the below step to avoid indirect modification exception -- note for developers  -_-
                        $metadata = collect($transaction['meta'])->jsonserialize();
                        $metadata['from'] = $customer->name;
                        $transaction['meta'] = $metadata;
                        $transaction->save();
                    }
                }
            }
        }

        event(new ShomosReservationUpdated($reservation));
        event(new ReservationUpdated($reservation));
        $response = [
            'success' => true,
            'message' => 'updated'
        ];
        return response()->json($response);
    }

    public function confirmGroupReservation(Request $request)
    {
        //for making confirmation on group reservation from mytravel -> fandaqah
        $reservation = Reservation::find($request->reservation_id);

        $first_deposit_term = Term::where('team_id', $request->team_id)->where('type', 2)->first();

        $meta = [
            'category' => 'reservation',
            'statement' => 'Billed Online',
            'type' => $first_deposit_term ? $first_deposit_term->id : null,
            'payment_type' => 'credit',
            'note' => $request->meta['note'],
            'reference' => $request->reference,
            'date' => Carbon::now()->format('Y-m-d H:i'),
            'from' => $request['guest_name'],
            'employee' => null,
            'preprocessor' => $request->preprocessor
        ];

        $transaction = $reservation->depositFloat(floatval($request->amount), $meta, true, true);
        $reservation->status = 'confirmed';

        $reservation->save();
        $attachable_ids = $request->reservation_ids;
        foreach ($attachable_ids as $attachable_id) {
            $attachable = Reservation::find($attachable_id);
            $attachable->status = 'confirmed';
            $attachable->save();
        }

        $fromDate = Carbon::parse($reservation->date_in_time)->utc()->format('Y-m-d\TH:i:s.000\Z');
        $toDate = Carbon::parse($reservation->date_out_time)->utc()->format('Y-m-d\TH:i:s.000\Z');
        $orginal_to = new \DateTime($reservation->date_in_time);
        $orginal_from = new \DateTime($reservation->date_out_time);
        //formate $orginal_to and $orginal_from to (y-m-d) to get the number of nights
        $fromDate = Carbon::parse($reservation->date_in_time)->format('Y-m-d');
        $toDate = Carbon::parse($reservation->date_out_time)->format('Y-m-d');

        $total_nighs = $orginal_to->diff($orginal_from)->days;


        $hijri_date = convertGregorianToHijriDate($reservation->created_at);

        $group_invoice_data = new Request();
        $group_invoice_data->merge([

            "id" => $reservation->id,
            "team_id" => $request->team_id,
            "attachable_id" => null,
            "all_grouped_reservations_ids" => $request->reservation_ids,
            "dates_calculations" => [
                "start_date" => $fromDate,
                "end_date" => $toDate,
                "first_checked_in_date" => null,
                "last_checked_out_date" => null,
                "nights" => $total_nighs,
            ],
            "from_date" => $fromDate,
            "to_date" => $toDate,
            "note" => null,
            "amount" => $request->amount,
            "company_id" => $reservation->company_id,
        ]);
        if($request->enable_invoicing){

            $group_invoice = $this->createGroupInvoice($group_invoice_data);
        }
        return response()->json(['success' => true, 'hijri_date' => $hijri_date]);
    }
    public function createGroupInvoice(Request $request)
    {
        $team_id = $request->team_id;
        $company = Company::find($request->get('company_id'));
        $invoice = new ReservationInvoice();
        $invoice->team_id = $team_id;
        $invoice->from = date(Carbon::createFromTimestamp(strtotime($request->get('from_date')))->format('Y-m-d'));
        $invoice->to = date(Carbon::parse($request->get('to_date'))->addDay()->format('Y-m-d'));
        $counter = TeamCounter::where('team_id', $team_id)->first();
        $next = $counter->invoice_num;
        $invoice->number = $next;
        $invoice->note = $request->note;
        $counter->last_invoice_number = $next;
        $to = new \DateTime($invoice->to);
        $from = new \DateTime($invoice->from);
        $parsedFrom = Carbon::parse($from);
        $parsedTo = Carbon::parse($to);
        $period = CarbonPeriod::create($parsedFrom, $parsedTo);
        $main_sub_total = 0;
        $reservations = Reservation::whereIn('id', $request->get('all_grouped_reservations_ids'))->get();
        $reservations_subtotals_arr = [];
        $reservations_ewa_percentage_arr = [];
        $reservations_vat_percentage_arr = [];
        $reservations_tourism_percentage_arr = [];
        $balances = [];
        $periods = [];
        $has_at_least_one_vat = false;
        $reservations_units_holder = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservation_scoped_sub_total = 0;
                if (is_null($reservation->attachable_id)) {
                    $invoice->reservation_id = $reservation->id;
                }
                $reservations_ewa_percentage_arr[$reservation->id] = $reservation->old_prices['ewa_parentage'];
                $reservations_vat_percentage_arr[$reservation->id] = $reservation->old_prices['vat_parentage'];
                $reservations_tourism_percentage_arr[$reservation->id] = $reservation->old_prices['tourism_percentage'];
                $balances[] = $reservation->wallet->decimal_places == 3 ? $reservation->balance / 1000 : $reservation->balance / 100;
                $reservations_units_holder[$reservation->id][] =  [
                    'id' => $reservation->unit->id,
                    'name' => json_decode($reservation->unit->getOriginal('name')),
                    'unit_number' => $reservation->unit->unit_number
                ];
                if ($reservation->vat_total) {
                    $has_at_least_one_vat = true;
                }
                foreach ($period as $date) {
                    if ($reservation->rent_type == 1) {
                        // it's a daily reservation
                        foreach ($reservation->prices['days'] as $obj) {
                            if (Carbon::parse($obj['date'])->format('Y-m-d') == $date->format('Y-m-d')) {
                                $unit_category_id = $reservation->unit->unit_category_id;
                                $specialPrice = SpecialPrice::where('unit_category_id', $unit_category_id)->where('start_date', '<=', $obj['date'])->where('end_date', '>=', $obj['date'])->where('enabled', true)->first();
                                $dayName = Carbon::parse($obj['date'])->format('l');
                                if($specialPrice){
                                    $main_sub_total = (float) number_format($specialPrice->days_prices[$dayName], 2, '.', '');
                                    $reservation_scoped_sub_total = (float) number_format($specialPrice->days_prices[$dayName], 2, '.', '');

                                    if($main_sub_total == null && $reservation_scoped_sub_total == null){
                                        $main_sub_total = $obj['price'];
                                        $reservation_scoped_sub_total = $obj['price'];

                                    }
                                }else{
                                    $main_sub_total = $obj['price'];
                                    $reservation_scoped_sub_total = $obj['price'];
                                }
                                $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                $periods[$reservation->id][] = $date->format('Y-m-d');
                            }
                        }
                    } else {
                        // it's a monthly reservation
                        // i need to construct an array that will hold days and create period here for monthly reservations
                        $monthlyDays = [];
                        $monthlyStart = new \DateTime($reservation->date_in);
                        $monthlyEnd = new \DateTime($reservation->date_out);
                        $parsedMonthlyStart = Carbon::parse($monthlyStart);
                        $parsedMonthlyEnd = Carbon::parse($monthlyEnd);

                        if ($parsedMonthlyStart != $parsedMonthlyEnd) {
                            $parsedMonthlyEnd->subDay();
                        }

                        $monthlyPeriod = CarbonPeriod::create($parsedMonthlyStart, $parsedMonthlyEnd);
                        $single_night_in_month_price = $reservation->sub_total / $reservation->nights;
                        foreach ($monthlyPeriod as $obj) {
                            if (Carbon::parse($obj)->format('Y-m-d') == $date->format('Y-m-d')) {
                                $main_sub_total += $single_night_in_month_price;
                                $reservation_scoped_sub_total = $single_night_in_month_price;
                                $reservations_subtotals_arr[$reservation->id][] = $reservation_scoped_sub_total;
                                $periods[$reservation->id][] = $date->format('Y-m-d');
                            }
                        }
                    }
                }
            }
        }
        $reservations_minified = [];
        $reservations_minified_keyed = [];
        $services_sum = 0;
        $services = [];
        $invoice_total_reservation_amount = 0;
        foreach ($reservations_subtotals_arr as $key => $subtotals_arr) {
            $current_reservation_ewa_percentage =  $reservations_ewa_percentage_arr[$key];
            $current_reservation_vat_percentage =  $reservations_vat_percentage_arr[$key];
            $current_reservation_tourism_percentage =  $reservations_tourism_percentage_arr[$key];
            $current_reservation_sub_total = array_sum($subtotals_arr);
            $ewa_amount = ($current_reservation_sub_total / 100) * $current_reservation_ewa_percentage;
            $ttx_amount = ($current_reservation_sub_total / 100) * $current_reservation_tourism_percentage;
            $vat_amount = (($current_reservation_sub_total + $ewa_amount) / 100) * $current_reservation_vat_percentage;
            $reservation_std = new stdClass();
            $reservation_std->id = $key;
            $reservation_std->unit = $reservations_units_holder[$key];
            $reservation_std->ewa = (float) number_format($ewa_amount, 2, '.', '');
            $reservation_std->ttx = (float) number_format($ttx_amount, 2, '.', '');
            $reservation_std->vat = (float) number_format($vat_amount, 2, '.', '');
            $reservation_std->sub_total = (float) number_format($current_reservation_sub_total, 2, '.', '');
            $reservation_std->total_price = (float) number_format($current_reservation_sub_total + $ewa_amount + $ttx_amount + $vat_amount, 2, '.', '');
            $reservations_minified[] = $reservation_std;

            $reservation_std_keyed = new stdClass();
            $reservation_std_keyed->id = $key;
            $reservation_std_keyed->ewa = (float) number_format($ewa_amount, 2, '.', '');
            $reservation_std_keyed->ttx = (float) number_format($ttx_amount, 2, '.', '');
            $reservation_std_keyed->vat = (float) number_format($vat_amount, 2, '.', '');
            $reservation_std_keyed->sub_total = (float) number_format($current_reservation_sub_total, 2, '.', '');
            $reservation_std_keyed->total_price = (float) number_format($current_reservation_sub_total + $ewa_amount + $ttx_amount + $vat_amount, 2, '.', '');
            $reservations_minified_keyed[$key][] = $reservation_std_keyed;
            $invoice_total_reservation_amount += $reservation_std->total_price;
        }

        /**
         * The part of attaching services to invoices - very important
         */
        $maximum_possible_date_to_as_invoice = Carbon::parse($request->get('dates_calculations')['end_date'])->subDay()->format('Y-m-d');
        $grouped_reservations_ids = $request->get('all_grouped_reservations_ids');;
        if ($invoice->to == $maximum_possible_date_to_as_invoice) {
            $filterServicesForGroupReservationInoice = $this->filterServicesForGroupReservationInvoice($grouped_reservations_ids, $from, $to, true);
        } else {
            $filterServicesForGroupReservationInoice = $this->filterServicesForGroupReservationInvoice($grouped_reservations_ids, $from, $to);
        }

        $services_sum  = abs($filterServicesForGroupReservationInoice['servicesSum']);
        if (isset($filterServicesForGroupReservationInoice['services'])) {
            foreach ($filterServicesForGroupReservationInoice['services'] as $service) {
                $services[] = $service;
            }
        }

        $invoice_total_amount_with_services = (float) number_format($invoice_total_reservation_amount + $services_sum, 2, '.', '');

        $custom_reservations = [];
        if (count($reservations)) {
            foreach ($reservations as $reservation) {
                $reservationSkeleton = new stdClass();
                $reservationSkeleton->id = $reservation->id;
                $reservationSkeleton->attachable_id = $reservation->attachable_id;
                $reservationSkeleton->number = $reservation->number;
                $reservationSkeleton->ewa_total = $reservation->ewa_total;
                $reservationSkeleton->vat_total = $reservation->vat_total;
                $reservationSkeleton->sub_total = $reservation->sub_total;
                $reservationSkeleton->total_price = $reservation->total_price;
                $reservationSkeleton->pre_included_services_total = 0;
                $reservationSkeleton->unit =  [
                    'id' => $reservation->unit->id,
                    'name' => json_decode($reservation->unit->getOriginal('name')),
                    'unit_number' => $reservation->unit->unit_number
                ];
                $reservationSkeleton->customer =  $reservation->customer ?  [
                    'id' => $reservation->customer->id,
                    'name' => $reservation->customer->name,
                    'id_number' => $reservation->customer->id_number,
                    'phone' => $reservation->customer->phone,
                ] : null;

                $reservationSkeleton->old_prices = [
                    'ewa_parentage' => $reservation->old_prices['ewa_parentage'],
                    'vat_parentage' => $reservation->old_prices['vat_parentage'],
                ];
                $reservationSkeleton->rent_type = $reservation->rent_type;
                $custom_reservations[] = $reservationSkeleton;
            }
        }

        $final_data = [
            'amount' => (float) number_format($request->amount, 2, '.', ''),
            'company' => $company,
            'services' => $services,
            'extra_addon' => null,
            'reservations' => $custom_reservations,
            'group_balance' => array_sum($balances),
            'has_at_least_one_vat' => $has_at_least_one_vat,
            'reservations_minified' => $reservations_minified,
            'reservations_minified_keyed' => $reservations_minified_keyed,
            'periods' => $periods,
            'transactions_ids' => $filterServicesForGroupReservationInoice['transactions_ids']
        ];

        $invoice->data = $final_data;
        $invoice->is_group_reservation = 1;
        $user = User::updateOrCreate([
            'email' => "info@dyafa.com"
        ], [
            'current_team_id' => $team_id,
            'name' => 'Dyafa Booking Engine',
            'phone' => "+966558439000",
            'billing_address' => "Prince Turky Street",
            'password' => bcrypt('fandaqah2024'),
        ]);
        $dyafa_user_id = $user->id;
        $invoice->created_by = $dyafa_user_id;
        $counter->save();

        $invoice = $this->syncInvoiceOnCreate($invoice, "tax invoice", "invoice", "0", $team_id, $dyafa_user_id);
        $invoice->save();
        return response()->json(['invoice' => $invoice->load('invoiceCreditNote')]);
    }
    public function syncInvoiceOnCreate(ReservationInvoice $invoice, $invoice_type, $invoice_sub_type, $mark_credit_notes_as_sent, $team_id, $my_travel_user_id)
    {
        //reset in order to update it with newer payload
        $invoice->is_reported_to_zatca = null;
        $integration = Integration::findByKeyAndTeamId('ZatcaPhaseTwo', $team_id);

        if (!isset($integration[0]->values)) {
            return $invoice;
        } else {
            $total_amount_excl_tax = 0;
            $total_amount_incl_tax = 0;
            $total_tax_amount = 0;
            $items = [];

            //  $org = auth()->user()->getSupplierEGS();
            // insted of auth()->user() we will use the user with id 22
            $auth_user = User::find($my_travel_user_id);

            $org = $this->getSupplierEGS($team_id);


            if ($org == null) {
                return $invoice;
            }
            $credential = (object) json_decode($integration)[0]->values;
            $credential = json_decode($credential->scalar);

            $invoice_type = 'tax invoice';

            $zatcaInvoice = new Invoice($credential->username, $credential->password, $invoice_type, $invoice_sub_type, $org);

            $invoice_data = (object) $invoice->data;

            $company = (object) array(
                "name" => "MyTravel",
                "tax_number" => "",
                "street" => "Prince Turki",
                "building" => "1000",
                "city_subdivision" => "South Khobar",
                "city" => "Khobar",
                "postal_zone" => "14455",
                "countryCode" => "SA"

            );
            // response()->json($company->countryCode);
            $zatcaInvoice->setCustomerInformation(
                $company->tax_number ?? "",
                $company->name ?? "",
                $company->countryCode ?? "SA",
                $company->street ?? "",
                $company->building ?? "",
                $company->city_subdivision ?? "",
                $company->city ?? "",
                $company->postal_zone ?? ""
            );


            //group reservations invoice

            if (isset($invoice_data->reservations_minified)) {
                foreach ($invoice_data->reservations_minified as $key => $reservation) {
                    $prices = (object) $reservation;
                    $total_amount_excl_tax =  number_format(floatval($prices->sub_total ?? 0.0), 2, '.', '') + $total_amount_excl_tax;
                    $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->vat ?? 0.0) + $total_amount_incl_tax;
                    $total_tax_amount = number_format(floatval($prices->vat ?? 0.0), 2, '.', '') + $total_tax_amount;
                }
                //single reservation invoice
            } elseif (isset($invoice->reservation)) {
                $prices = (object) $invoice->reservation->prices;
                $total_amount_excl_tax = number_format(floatval($prices->sub_total ?? 0.0), 2, '.', '') + $total_amount_excl_tax;
                $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->total_vat ?? 0.0) + $total_amount_incl_tax;
                $total_tax_amount =  number_format(floatval($prices->total_vat ?? 0.0), 2, '.', '') + $total_tax_amount;
            }

            $total_amount_excl_tax = number_format((float)$total_amount_excl_tax, 2, '.', '');
            $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');
            $total_tax_amount = number_format((float)$total_tax_amount, 2, '.', '');

            $zatcaInvoice->setTaxTotal($total_tax_amount ?? "0.0", $total_amount_excl_tax ?? "0.0");
            $zatcaInvoice->setMonetaryTotal($total_amount_incl_tax ?? "0.0", $total_amount_excl_tax ?? "0.0", null, null);
            //quantity -> nights
            //total_amount
            if (isset($invoice_data->reservations)) {
                foreach ($invoice_data->reservations as $key => $reservation_item) {
                    $reservation_item = (object) $reservation_item;
                    $unit = (object) $invoice->reservation->unit->name;
                    $prices =  (object) $invoice_data->reservations_minified[$key];

                    $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->vat ?? 0.0);
                    $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');

                    array_push($items, array(
                        "quantity" => '1',
                        "total_amount_excl_tax" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                        "total_tax_amount" => number_format((float)$prices->vat ?? 0.0, 2, '.', ''),
                        "total_amount_incl_tax" => $total_amount_incl_tax,
                        "item_name" => $unit->scalar ?? "",
                        "item_cost" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                        "item_discount" => '0.0'
                    ));
                }
            } elseif (isset($invoice->reservation)) {
                $unit = (object) $invoice->reservation->unit->name;
                // return response()->json($unit);
                $prices = (object) $invoice->reservation->prices;

                $total_amount_incl_tax = floatval($prices->sub_total ?? 0.0) + floatval($prices->total_vat ?? 0.0);
                $total_amount_incl_tax = number_format((float)$total_amount_incl_tax, 2, '.', '');

                array_push($items, array(
                    "quantity" => '1',
                    "total_amount_excl_tax" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                    "total_tax_amount" => number_format((float)$prices->total_vat ?? 0.0, 2, '.', ''),
                    "total_amount_incl_tax" => $total_amount_incl_tax,
                    "item_name" => $unit->scalar ?? "",
                    "item_cost" => number_format((float)$prices->sub_total ?? 0.0, 2, '.', ''),
                    "item_discount" => '0.0'
                ));
            }

            $zatcaInvoice->setItems($items);

            $datetime = Carbon::parse($invoice->created_at);
            $zatcaInvoice->setIssueDateTime($datetime->format('Y-m-d'), $datetime->format('h:i:s'));

            $zatcaInvoice->setInvoiceBillingReferenceId($invoice->number);

            if ($zatcaInvoice->checkIfCreditNote($invoice_sub_type)) {
                $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice->number);
                $zatcaInvoice->setPaymentInstruction("Returned");
            }

            //return response()->json($zatcaInvoice);
            $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

            //activity()->performedOn((new IntegrationSettings()))->log(__('Team Id :TEAM has reported Invoice#:INVOICE to zatca successfully', ['team' => $key, 'invoice' => '']));
            if (
                !isset($compliant_invoice->data->base64_signed_invoice_string) &&
                !isset($compliant_invoice->data->invoice_hash) &&
                !isset($compliant_invoice->data->uuid)
            ) {
                activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice->id]));
                return $invoice;
            }

            $response = $zatcaInvoice->reportInvoice($compliant_invoice);

            if (!isset($response->status) || $response->status !== 200 || $response == null) {
                return $invoice;
            }

            if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
                $invoice->is_reported_to_zatca = $response->data;
            }

            if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
                $invoice->is_reported_to_zatca = $response->data;
            }

            //since invoice has been credit we check credit notes and mark them as synced with zatca
            if ($mark_credit_notes_as_sent == "1") {
                $invoice_credit_note = InvoiceCreditNote::where("reservation_invoice_id", $invoice->id)->first();

                if ($invoice_credit_note) {
                    $zatcaInvoice->setCanceledInvoiceBillingReferenceId($invoice->number);
                    $zatcaInvoice->setPaymentInstruction("Returned");
                    $zatcaInvoice->setInvoiceSubType("credit note");
                    $compliant_invoice = $zatcaInvoice->getCompliantInvoice();

                    if (
                        !isset($compliant_invoice->data->base64_signed_invoice_string) &&
                        !isset($compliant_invoice->data->invoice_hash) &&
                        !isset($compliant_invoice->data->uuid)
                    ) {
                        activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID push to zatca has been failed', ['id' => $invoice->id]));
                        return $invoice;
                    }

                    $response = $zatcaInvoice->reportInvoice($compliant_invoice);

                    if (!isset($response->status) || $response->status !== 200 || $response == null) {
                        return response()->json($response, 500);
                    }

                    if (isset($response->data->reportingStatus) && $response->data->reportingStatus == "REPORTED") {
                        $invoice_credit_note->is_reported_to_zatca = json_encode($response->data);
                    }

                    if (isset($response->data->clearanceStatus) && $response->data->clearanceStatus == "CLEARED") {
                        $invoice_credit_note->is_reported_to_zatca = json_encode($response->data);
                    }

                    $invoice_credit_note->save();

                    activity()->performedOn((new InvoiceCreditNote()))->log(__('Invoice :ID credit note has been pushed to zatca successfully', ['id' => $invoice->id]));
                }
            }

            //emit activity
            activity()->performedOn((new ReservationInvoice()))->log(__('Invoice :ID has been pushed to zatca successfully', ['id' => $invoice->id]));

            return $invoice;
        }
    }
    public function getSupplierEGS($team_id)
    {


        $settings = (object) \App\Setting::where('team_id', $team_id)->whereIn('key', ['hotel_address', 'hotel_email', 'hotel_phone_number', 'tax_number', 'city', 'district', 'street', 'commercial_register'])->pluck('value', 'key')->all();
        //get from table teams where id = $team_id
        $team = \App\Team::find($team_id);


        if (!isset($settings->tax_number)) {
            return null;
        }

        return array(
            "model" => "Team-" . $team_id,
            "uuid" => '1-' . $team_id . '-' . $settings->tax_number,
            "solution_name" => "Sol-" . $team_id,
            "vat_number" => $settings->tax_number ?? "",
            "location" => array(
                "city" => $settings->city ?? "",
                "city_subdivision" => $settings->district ?? "",
                "street" => $settings->street ?? "",
                "plot_identification" => $settings->plot_no ?? "",
                "building" => $settings->building ?? "",
                "postal_zone" => $settings->postal_zone ?? "",
            ),
            "branch_industry" => $settings->industry ?? "hospitality",
            "branch_name" =>  $settings->branch ?? "Test branch",
            "org_name" => $team->name,
            "taxpayer_name" => $team->name,
            "CRN_number" => $settings->commercial_register ?? "",
            "production" => env('APP_ENV') == 'production' ? "1" : "0",
            "team_id" => $team_id
        );
    }
    private function filterServicesForGroupReservationInvoice($reservations_ids, $from, $to, $is_last_invoice = false)
    {
        $services = [];
        $servicesSum = [];
        if ($is_last_invoice) {
            $servicesTransactions  = Transaction::with('wallet')
                ->where('payable_type', 'App\\Reservation')
                ->whereIn('payable_id', $reservations_ids)
                ->where('is_public', 0)
                ->where('meta->category', 'service')
                ->where('is_attached_to_invoice', 0)
                ->get();
        } else {
            $servicesTransactions  = Transaction::with('wallet')
                ->where('payable_type', 'App\\Reservation')
                ->whereIn('payable_id', $reservations_ids)
                ->where('is_public', 0)
                ->where('meta->category', 'service')
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->get();
        }
        // $servicesTransactions  = Transaction::with('wallet')->where('payable_type', 'App\\Reservation')->where('payable_id', $reservation_id)->where('is_public', 0)->where('meta->category', 'service')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->get();
        $transactions_ids = [];
        if (count($servicesTransactions)) {
            foreach ($servicesTransactions as $transaction) {
                $servicesSum[] = $transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100);
                foreach ($transaction->meta['services'] as $serviceObj) {
                    $services[] = $serviceObj;
                    $transaction->is_attached_to_invoice = 1;
                    $transactions_ids[] = $transaction->id;
                    $transaction->save();
                }
            }
        }
        return ['services' => $services, 'servicesSum' => array_sum($servicesSum), 'transactions_ids' => $transactions_ids];
    }
    public function determineIfUnitHasActualReservationAccordingToStartDate($unit, $date_start, $currentDatesHolder)
    {
        /**
         * Here i will put my logic to handle the intersection based on the unit selected
         */

        $unitReservations = Reservation::where('unit_id', $unit->id)
            ->whereNUll('checked_out')
            ->whereIn('status', ['confirmed', 'awaiting-payment', 'awaiting-confirmation'])
            ->whereNull('deleted_at')
            ->get();

        $unitDatesHolder = [];
        if (count($unitReservations)) {
            foreach ($unitReservations as $unitReservation) {
                $start  = Carbon::parse($unitReservation->date_in);
                $end  = Carbon::parse($unitReservation->date_out);
                if ($start != $end) {
                    $end->subDay();
                }
                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    if (!in_array($date->format('Y-m-d'), $unitDatesHolder)) {
                        $unitDatesHolder[] = $date->format('Y-m-d');
                    }
                }
            }
        }


        /**
         * Checking the overlapping the right way -_-
         */
        if (array_intersect($currentDatesHolder, $unitDatesHolder)) {
            $has_reservation = true;
        } else {
            $has_reservation = false;
        }

        return $has_reservation;
    }

    public function linkImagesApi($team_id)
    {

        $settings = WebsiteSetting::where('team_id', $team_id)->first();
        $webiste_id = $settings->id;
        $gallery = WebsiteGallery::where('website_id', $webiste_id)->get();

        if ($gallery->isEmpty()) {

            return response()->json(['message' => 'No images found'], 404);
        } else {

            $team = Team::find($team_id);
            $mytravel_hotel_id = $team->mytravel_hotel_id;

            $photo_types = [];
            foreach ($gallery as $photo_type) {
                $images = ['type' => $photo_type->type, 'image_path' => $photo_type->path, 'hotel_id' => $mytravel_hotel_id, 'team_id' => $team_id];
                array_push($photo_types, $images);
            }
        }

        return response()->json($photo_types);
    }
    public function getReservationInformation(Request $request, $team_id, $unit_id)
    {
        $reservation = Reservation::where("team_id", $team_id)->where('unit_id', $unit_id)
            ->where('status', 'confirmed')
            ->where('date_in', $request->date_in)
            ->where('date_out', $request->date_out)
            ->first();

        if ($reservation) {

            $customer = Customer::find($reservation->customer_id);
            $data = [

                'guest_name' => $customer->name,
                'guest_phone' => $customer->phone,
                'guest_email' => $customer->email,
                'guest_address' => $customer->address,

            ];
            return response()->json($data);
        } else {

            return response()->json(['message' => 'No reservation found'], 404);
        }
    }

    public function deleteDraftedReservation(Request $request)
    {
        $reservation = Reservation::find($request->reservation_id);
        if ($reservation == null) {
            return response()->json(['message' => 'Reservation not found'], 404);
        } else {
            $reservation->delete();
            return response()->json(['message' => 'Reservation deleted successfully']);
        }
    }

    public function deleteDraftedGroupReservation(Request $request)
    {
        $reservation = Reservation::find($request->reservation_id);
        if ($reservation == null) {
            return response()->json(['message' => 'Reservation not found'], 404);
        } else {
            $reservation->delete();
            return response()->json(['message' => 'Reservation deleted successfully']);
        }
    }

    public function checkConnection()
    {
        return response()->json(['message' => 'Connected successfully']);
    }




    public function freezeTransactions(){
        // Get all team_ids where "enable_business_day_freeze" is 1
        $teams = DB::table('settings')
            ->where('key', 'enable_business_day_freeze')
            ->where('value', 1)
            ->pluck('team_id');  // Get an array of team_ids

        foreach ($teams as $teamId) {
            // Get the business_day_freeze_date for each team
            $freezeDate = DB::table('settings')
                ->where('team_id', $teamId)
                ->where('key', 'business_day_freeze_date')
                ->value('value');
            // dd($freezeDate);
                if ($freezeDate) {
                    // Parse the freeze date (includes date and time)
                    $freezeDate = Carbon::parse($freezeDate);
                    // Compare the freeze date with the current timestamp (including hour)
                    if (true) {

                       // Get the day before the freeze date, keeping the same time
                    //    $dayBeforeFreeze = $freezeDate->copy()->subDay();

                       $filters = [
                        //    'by_date_from' => "2025-01-20 12:00",
                           'by_date_to' => $freezeDate->format('Y-m-d H:i'),

                       ];

                       $transactions = DB::table('transactions as tr')
                            ->select('tr.id', 'tr.payable_id', 'tr.payable_type', 'tr.amount', 'tr.wallet_id', 'tr.is_freezed', 'tr.created_at', 'tr.updated_at')
                            ->where('tr.team_id', $teamId)
                            ->where('tr.amount', '!=', 0)
                            ->where('tr.is_freezed', 0)
                            ->whereNull('tr.deleted_at')
                            ->when(isset($filters['by_date_to']), function ($query) use ($filters) {
                                $to = Carbon::parse($filters['by_date_to'])->format('Y-m-d H:i');
                                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(tr.meta, '$.date')) <= ?", [$to]);
                            })
                            ->orderByDesc('tr.number')
                            ->pluck('tr.id');

                        if ($transactions->count() > 0) {
                            $transactions = Transaction::whereIn('id', $transactions)->get();
                            foreach ($transactions as $transaction) {
                                $transaction->is_freezed = 1;
                                $transaction->save();
                            }

                        }else{

                            // return response()->json(['message' => 'No transactions to freeze']);
                        }

                        // Increase by one day while keeping the time
                        $freezeHour = $freezeDate->hour;
                        $newDate = Carbon::now()->setHour($freezeHour)->setMinute(0)->setSecond(0);
                        $newFreezeDate =$newDate;
                        dd($newFreezeDate);
                        // Update the business_day_freeze_date in the database
                        DB::table('settings')
                            ->where('team_id', $teamId)
                            ->where('key', 'business_day_freeze_date')
                            ->update(['value' => $newFreezeDate->toDateTimeString()]);
                        return response()->json(['message' => 'Transactions freezed successfully']);
                    } else {

                        return response()->json(['message' => 'date is not the same']);
                    }
                } else {

                    return response()->json(['message' => 'No transactions to freeze']);
                }
        }
    }

    public function rating()
    {

        $reservation = Reservation::withOutGlobalScope('team_id')->find(1062889);



        $settings = DB::table('settings')
            ->whereIn('key', [
                'introduction_text',
                'text_of_the_first_question',
                'enable_first_question',
                'text_of_the_second_question',
                'enable_second_question',
                'text_of_question_three',
                'enable_question_three',
                'text_of_question_four',
                'enable_question_four',
                'text_of_question_five',
                'enable_question_five',
                'the_text_of_the_sixth_question',
                'enable_sixth_question',
                'the_text_of_the_seventh_question',
                'enable_seventh_question',
                'the_text_of_the_eighth_question',
                'enable_eighth_question',
                'the_text_of_the_ninth_question',
                'enable_ninth_question',
                'the_text_of_the_tenth_question',
                'enable_tenth_question',
                'first_custom_message',
                'enable_first_custom_message',
                'second_custom_message',
                'enable_second_custom_message',
                'title_of_the_first_question',
                'title_of_the_second_question',
                'title_of_the_third_question',
                'title_of_the_fourth_question',
                'title_of_the_fifth_question',
                'title_of_the_sixth_question',
                'title_of_the_seventh_question',
                'title_of_the_eighth_question',
                'title_of_the_ninth_question',
                'title_of_the_tenth_question',

            ])
            ->where('team_id', '=', $reservation->team_id)->pluck('value', 'key');
            if (!$reservation || !is_null($reservation->rating)) {
                //            return redirect('/');
                if(!$reservation->rating->status){
                    return view('edit-rating', ['reservation' => $reservation, 'settings' => $settings]);
                }else{

                    return view('edit-rating', ['reservation' => $reservation, 'settings' => $settings]);
                }
            }
        //        $reservation->unit = Unit::withTrashed()->find($reservation->unit_id);
        return view('rating', ['reservation' => $reservation, 'settings' => $settings]);
    }
}
