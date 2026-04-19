<?php

/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 9/22/19
 * Time: 4:03 PM
 */

namespace App\Http\Controllers\API\Corneer;

use Log;
use App\City;
use App\Team;
use App\Unit;
use App\Rating;
// import log
use App\Source;
use App\Company;
use App\Setting;
use App\Customer;
use Carbon\Carbon;
use App\UnitOption;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\WebsitePage;
use App\UnitCategory;
use GuzzleHttp\Client;
use Carbon\CarbonPeriod;
use App\Handlers\Settings;
use App\OnlineReservation;
use App\PublicApiConsumer;
use App\Mail\NewReservation;
use Illuminate\Http\Request;
use App\Events\ReservationCreated;
use App\Mail\ReservationCancelled;
use Illuminate\Support\Facades\DB;
use Liliom\Unifonic\UnifonicFacade;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Spatie\MediaLibrary\Models\Media;
use App\Events\SmsIntegrationDownEvent;
use App\Events\OnlineReservationCreated;
use App\Http\Requests\AddCompanyRequest;
use App\Events\OnlineReservationConfirmed;
use App\Http\Resources\Corneer\UnitResource;
use App\Handlers\SettingsRegisteringListener;
use App\Http\Resources\Corneer\PagesResource;
use App\Http\Resources\Corneer\CitiesResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\OnlineReservationRequest;
use App\Http\Resources\Corneer\CustomerResource;
use App\Http\Resources\Corneer\UnitCategoryResource;
use App\Http\Resources\Corneer\UnitMinifiedResource;
use App\Http\Resources\Corneer\RelatedHotelsResource;
use App\Http\Resources\Corneer\OnlineReservationResource;
use Surelab\Settings\Http\Controllers\SettingsController;
use App\Mail\Customer\AwaitingConfirmationReservationMail;
use App\Http\Resources\CustomerReviews\WebsiteRatingResource;
use App\Http\Resources\Corneer\FeaturedUnitCategoriesResource;
use App\Http\Resources\CustomerReviews\RatingMinifiedResource;
use App\Http\Resources\Corneer\FeaturedUnitCategoryFullResource;
use Surelab\Settings\Http\Controllers\WebsiteSettingsController;
use App\Http\Resources\Corneer\UnitCategoryWithSpecialPricesResource;
use App\SpecialPrice;

class UnitController extends Controller
{
    public function list($date_start, $date_end)
    {
        return  UnitResource::collection(
            Unit::whereEnabled(true)->whereTeamId(\View::getShared()['currentTeamId'])->where('status', '=', 1)->available($date_start)->paginate()
        );
    }
    public function index(Request $request)
    {
        return  UnitResource::collection(
            Unit::whereEnabled(true)->whereTeamId(\View::getShared()['currentTeamId'])->where('status', '=', 1)->paginate()
        );
    }

    //mytravel-code
    public function getReservationMyTravel(Request $request, $commingresevation = null)
    {
        $unitCategory = $request['category_id'][0];
        $unit = Unit::where('unit_category_id', $unitCategory)->first();
        $date_in = $request['check_in'];
        $date_in = Carbon::parse($date_in);

        $date_out = $request['check_out'];
        $date_out = Carbon::parse($date_out);
        $numberOfNights = $date_out->diffInDays($date_in);

        $teamid = $request['team_id'];
        $tax = (getVatPercentageForUnit($teamid) === null) ? 0 : getVatPercentageForUnit($teamid);
        $ewa = (getEwaPercentageForUnit($teamid) === null) ? 0 : getEwaPercentageForUnit($teamid);
        $tax = $tax / 100;
        $ewa = $ewa / 100;

        $totalPrice = 0;
        $totalSubTotal = 0;
        $totalEwa = 0;
        $totalVat = 0;
        $totalPriceRaw = 0;
        $specials = [];
        $dates = $date_in;
        // Iterate over the number of nights
        for ($i = 0; $i < $numberOfNights; $i++) {
            $dayName = $date_in->format('l');

            // Fetch special price for the current day
            $specialPrice = SpecialPrice::where('unit_category_id', $unitCategory)
                                        ->where('start_date', '<=', $date_in)
                                        ->where('end_date', '>=', $date_in)
                                        ->where('enabled', true)
                                        ->first();
            if ($specialPrice) {
                // Check if there's a price for the current day
                if (!is_null($specialPrice->days_prices[$dayName])) {
                    $dayPrice = $specialPrice->days_prices[$dayName];
                    // Calculate prices for the current day
                    $subTotal = $dayPrice;
                    $Ewa = $dayPrice * $ewa;

                    $price_before_vat = $dayPrice + $Ewa;
                    $Vat = ($price_before_vat) * $tax;
                    $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                    // Add to the total calculations
                    $totalPrice += $totalPriceRaw;
                    $totalSubTotal += $subTotal;
                    $totalEwa += $Ewa;
                    $totalVat += $Vat;
                    $specials[] = [
                        'date' => $date_in->format('Y-m-d'),
                        'price' => $dayPrice,

                    ];

                }else{
                    $unit_category = UnitCategory::where('id', $unit->unit_category_id)->first();
                    $day_Name_price = strtolower($dayName) . '_day_price';
                    $dayPrice = $unit_category->$day_Name_price;
                    // Calculate prices for the current day
                    $subTotal = $dayPrice;
                    $Ewa = $dayPrice * $ewa;

                    $price_before_vat = $dayPrice + $Ewa;
                    $Vat = ($price_before_vat) * $tax;
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
                    $Ewa = $dayPrice * $ewa;

                    $price_before_vat = $dayPrice + $Ewa;
                    $Vat = ($price_before_vat) * $tax;
                    $totalPriceRaw = $dayPrice + $Vat + $Ewa;

                    // Add to the total calculations
                    $totalPrice += $totalPriceRaw;
                    $totalSubTotal += $subTotal;
                    $totalEwa += $Ewa;
                    $totalVat += $Vat;
            }

            // Move to the next day
            $dates->addDay();
        }
        $transformedArray = [];
        foreach ($specials as $item) {
            $transformedArray[] = [
                'date' => $item['date'],
                'specialPrice' => $item['price']
            ];
        }

        $special_prices = json_encode($transformedArray);

        $aftertax = $totalPrice;

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

        $source_en = $request['source_en'];
        $source_ar = $request['source_ar'];
        $referance_id = $request['referance_id'];
        $matchThese = array('team_id' => $team_id, 'phone' => $phone);
        $customer = Customer::updateOrCreate(
            $matchThese,
            ['team_id' => $team_id,  'name' => $name,  'email' => $email,  'phone' => $phone,  'address' => $address]
        );

        $units = UnitCategory::find($unitCategory)->units;
        // return $units;
        if ($units) {
            $notAvailableDates = $this->getAvailabilityForBooking($unitCategory, $date_in,
            $date_out, $team_id);

            if (!in_array(Carbon::parse($request['check_in'])->format('Y-m-d'), $notAvailableDates)) {
                foreach ($units as $unit) {

                    // $hasIntersectionWorkable = $unit->has_intersection_check(Carbon::parse($request->get('check_in')) , Carbon::parse($request->get('check_out')));
                    $hasIntersectionWorkable = checkIfUnitHasReservation($unit->id, Carbon::parse($request['check_in']));
                    if (!$hasIntersectionWorkable) {
                        $source = Source::query()->where('name->en', $source_en)->where('team_id', $team_id)->first();
                        if (!$source) {
                            $source =  Source::create(['name' => ['en' => $source_en, 'ar' =>  $source_ar], 'team_id' =>  $team_id, 'deleteable' => 0]);
                        }
                        $team = Team::with('owner')->find($team_id);
                        if ($commingresevation != null) {
                            $reservation = $commingresevation;
                            $reservation->id = $commingresevation->id;
                        } else {

                            $reservation = new Reservation();
                        }
                        // $reservation->number = ;

                        $reservation->team_id = $team_id;
                        $reservation->unit_id = $unit->id;
                        $reservation->source_id =  $source->id;
                        $reservation->source_num = $referance_id;
                        $reservation->rent_type = 1;
                        $reservation->customer_id = $customer->id;
                        $reservation->date_in = Carbon::parse($request['check_in'])->format('Y-m-d');
                        $reservation->date_out = Carbon::parse($request['check_out'])->format('Y-m-d');
                        $reservation->is_online = 1;
                        $reservation->status = 'awaiting-payment';
                        $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
                        $reservation->total_price = $total_price;
                        $reservation->sub_total = (float)number_format($subtotal,  2, '.', '');
                        $reservation->vat_total = $total_vat;
                        $reservation->ewa_total = (float)number_format($total_ewa,  2, '.', '');
                        $reservation->purpose_of_visit = '';
                        $reservation->change_rate = 0;
                        $reservation->special_prices = $special_prices;
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
                            $data = [
                                'reservation_id' => $reservation->id,
                                'sub_total' => $reservation->sub_total,
                                'vat_total' => $reservation->vat_total,
                                'ewa_total' => $reservation->ewa_total,
                                'single' => true,
                            ];

                            $data = json_encode($data);
                            $add_company_request = new AddCompanyRequest();
                        $add_company_request->merge([
                            'team_id' => $request->team_id,
                            'name' => "Dyafa Booking Engine",
                            'phone' => "+966558439000",
                            'city' => "Alkhubar",
                            'address' => "Prince Turky Street",
                            'person_incharge_name' => "Dyafa Booking Engine",
                            'person_incharge_phone' => "+966558439000",
                            'email' => "booking@dyafa.com",
                            'tax_number' => "511423123300003",
                            'building_number' => "300",
                            'street_name' => "Prince Turky Street",
                            'reservation_id' => $reservation->id,
                            'reservation_type' => 'single',
                        ]);
                        $add_company = $this->storeCompany($add_company_request);


                            return $data;
                        }
                    }
                }
            }
        }
    }
    public function storeCompany(AddCompanyRequest $request)
    {

        $company = Company::updateOrCreate([
            'email' => $request->get('email')
        ], [
            'team_id' => $request->get('team_id'),
            'user_id' => $request->get('user_id'),
            'name' => $request->get('name'),
            'phone' => str_replace(' ', '', $request->get('phone')),
            'city' => $request->get('city'),
            'address' => $request->get('address'),
            'person_incharge_name' => $request->get('person_incharge_name'),
            'person_incharge_phone' => $request->get('person_incharge_phone'),
            'tax_number' => $request->get('tax_number'),
        ]);

        if ($request->has('reservation_id')) {
            $reservation = Reservation::find($request->get('reservation_id'));

            if ($request->get('reservation_type') == 'group') {
                // its a group reservation of entity type individual and needs a company
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
                    ->where('company_id', $reservation->company_id)
                    ->where(function ($query) use ($reservation, $main_reservation) {
                        return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                    })
                    ->where('status', 'confirmed')
                    ->whereNull('deleted_at')
                    ->get();

                if ($push_main_reservation_to_collection) {
                    $reservations->push($main_reservation);
                }

                if (count($reservations)) {
                    foreach ($reservations as $reservationObject) {
                        $reservationObject->company_id = $company->id;
                        $reservationObject->save();
                    }

                    return response()->json(['success' => true, 'reload_reservation' => true], Response::HTTP_CREATED);
                }
            } else {
                // its a single reservation
                $reservation->company_id = $company->id;
                $reservation->reservation_type = 'group';
                $reservation->save();

                return $company->id;
            }
        }
        return response()->json(['success' => true, 'company' => $company], Response::HTTP_CREATED);
    }
    //end-mytravel-code

    public function getUnit($id, $date_start, $date_end)
    {
        /** @var Unit $unit */
        $unit = Unit::find(Hashids::decode($id)[0]);

        $date_start = Carbon::parse($date_start);
        $date_end = Carbon::parse($date_end);
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        $result = [
            'id' => $unit->id,
            'hash'  =>  Hashids::encode($unit->id),
            'unit_number' => $unit->unit_number,
            'name' => $unit->name,
            'main_image'    =>  is_null($unit->getFirstMediaUrl('main')) || empty($unit->getFirstMediaUrl('main')) ? asset('images/placeholder.jpg') : $unit->getFirstMediaUrl('main'),
            'description' =>  $unit->description,
            'youtube_link' =>  $unit->youtube_link,
            'short_description' =>  $unit->short_description,
            'has_reservation' => count($unit->getReservations($date_start)) > 0,
            'prices' => [
                'day' => $unit->dayPrice($date_start->format('l')),
                'month' => $unit->monthPrice(),
                'hour' => $unit->hourPrice(),
            ],
            'reservation' => [
                'start_date' => $date_start->format('d-m-Y'),
                'end_date' => $date_end->format('d-m-Y'),
                'days' => $diff_days,
                'nights' => $diff_nights,
                'prices' => $unit->getDatesFromRange($date_start, $date_end),
            ],
            'reservations_date' => $unit->getReservationsDates(),
            'images'  =>  $this->transformMediaCollection($unit->getMedia('images')),
            'options'   =>  $unit->options(),
            'special_features'  =>  \App\UnitSpecialFeature::whereIn('id', explode(',', $unit->special_features))->pluck('name')->toArray(),
            'general_features'  =>  \App\UnitGeneralFeature::whereIn('id', explode(',', $unit->general_features))->pluck('name')->toArray(),
        ];

        return response()->json($result);
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
    public function getReservation($id)
    {
        /** @var OnlineReservation $reservation */
        $reservation = OnlineReservation::withoutGlobalScope('team_id')->find(Hashids::decode($id)[0]);

        //        $response = array_merge($reservation->toArray(), [
        //            'hash'  => Hashids::encode($date->id)
        //        ]);

        return response()->json($reservation->toArray());
    }
    public function getSettings()
    {
        $team = Team::find(\View::getShared()['currentTeamId']);

        return (new WebsiteSettingsController)->websiteSettings($team);
    }
    public function getTeam()
    {
        return response()->json(Team::find(\View::getShared()['currentTeamId'])->toArray());
    }
    public function indexUnitCategpries(Request $request)
    {

        return UnitCategoryResource::collection(
            UnitCategory::whereStatus(true)->whereTeamId(\View::getShared()['currentTeamId'])->whereHasUnits()->paginate()
        );
    }
    public function searchUnitCategories($date_start, $date_end)
    {
        return UnitCategoryResource::collection(
            UnitCategory::whereStatus(true)->where('show_in_website', 1)->whereTeamId(\View::getShared()['currentTeamId'])->whereHasUnits()->get()
        );
    }
    public function getUnitCategory($id, $date_start, $date_end)
    {

        $unitCategory = UnitCategory::find(Hashids::decode($id)[0]);

        $date_start = Carbon::parse($date_start);
        $date_end = Carbon::parse($date_end);
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        //        $result = [
        //            'id' => $unitCategory->id,
        //            'hash'  =>  Hashids::encode($unitCategory->id),
        //            'unit_number' => '#',
        //            'name' => $unitCategory->name,
        //            'main_image'    =>  is_null($unitCategory->getFirstMediaUrl('main')) || empty($unitCategory->getFirstMediaUrl('main')) ? asset('images/placeholder.jpg') : $unitCategory->getFirstMediaUrl('main'),
        //            'description' =>  $unitCategory->description,
        //            'youtube_link' =>  $unitCategory->youtube_link,
        //            'short_description' =>  $unitCategory->short_description,
        ////            'has_reservation' => count($unit->getReservations($date_start)) > 0,
        //            'prices' => [
        //                'day' => $unitCategory->dayPrice($date_start->format('l')),
        //                'month' => $unitCategory->monthPrice(),
        //            ],
        ////            'reservation' => [
        ////                'start_date' => $date_start->format('d-m-Y'),
        ////                'end_date' => $date_end->format('d-m-Y'),
        ////                'days' => $diff_days,
        ////                'nights' => $diff_nights,
        ////                'prices' => $unit->getDatesFromRange($date_start, $date_end),
        ////            ],
        ////            'reservations_date' => $unit->reservations_date,
        //            'images'  =>  $this->transformMediaCollection($unitCategory->getMedia('images')),
        //            'special_features'  =>  \App\UnitSpecialFeature::whereIn('id', explode(',', $unitCategory->special_features))->pluck('name')->toArray(),
        //            'general_features'  =>  \App\UnitGeneralFeature::whereIn('id', explode(',', $unitCategory->general_features))->pluck('name')->toArray(),
        //        ];


        return new UnitCategoryResource($unitCategory);
        //        return response()->json($result);
    }
    public function makeBooking(Request $request)
    {
        $customer = Customer::whereEmail($request->get('email'))->where('team_id', '=', \View::getShared()['currentTeamId'])->first();

        if (is_null($customer)) {
            $customer = Customer::create([
                'name' =>  $request->get('first_name') . ' ' . $request->get('last_name'),
                'email' =>  $request->get('email'),
                'phone' =>  $request->get('phone'),
                'team_id'   =>  \View::getShared()['currentTeamId']
            ]);
        }

        $unit_id = Hashids::decode($request->get('id'))[0];
        $unit = Unit::find($unit_id);

        // unit has been reserved while the guest of online website was intending to reserve
        // so if there was a space for other units under the category we will retry to book again
        // it there was no space ( no available units under this category ) then we will tell our guest that
        // during his time in this page a reservation was added and there are no available units in this categpry
        if ($unit->has_reservation(Carbon::parse($request->get('in_date')))) {
            return response()->json(['status' => 'unit_has_been_reserved']);
        }

        /** @var OnlineReservation $date */
        $date = OnlineReservation::create([
            'customer_id'   =>  $customer->id,
            'date_in'   =>  $request->get('in_date'),
            'date_out'   =>  $request->get('out_date'),
            'price'   =>  $request->get('price'),
            'nights'   =>  $request->get('nights'),
            'unit_id'   =>  Hashids::decode($request->get('id'))[0],
            'team_id'   =>  \View::getShared()['currentTeamId']
        ]);
        $reservation = $date;
        $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $reservation->team_id)->value('value');
        $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

        $subject = 'تفاصيل حجزك رقم ';
        $subject .= $reservation->number;
        $subject .= ' - بانتظار التاكيد - ';
        $subject .= $reservation->team->name;
        $url = $reservation->team->private_domain ? $reservation->team->private_domain :  $reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');

        $data = [
            'to' => $customer->email,
            'reply_to' => $hotelEmail,
            'subject' => __('Payment Receipt For Reservation : :number',['number' => $reservation->number], 'ar'),
            'html' => view('email.new_reservation')
                ->with(['reservation' =>  $reservation, 'owner' => $reservation->team->owner, 'url'    =>  $url])->render(),
        ];

        $send = sendMailUsingMailMicroservice($data);

        $subject = 'حجز جديد رقم ';
        $subject .= $reservation->number;
        $subject .= ' - بانتظار التاكيد - ';
        $subject .= $reservation->team->name;
        $data = [
            'to' => $date->team->owner->email,
            'reply_to' => $hotelEmail,
            'subject' => __('Payment Receipt For Reservation : :number',['number' => $reservation->number], 'ar'),
            'html' => view('email.new_reservation')
                ->with(['reservation' =>  $reservation, 'owner' => $reservation->team->owner, 'url'    =>  $url])->render(),
        ];
        $send = sendMailUsingMailMicroservice($data);

        // \Mail::to($customer)->send(new NewReservation($date));
        // \Mail::to($date->team->owner)->send(new NewReservation($date, true));

        $response = array_merge($date->toArray(), [
            'hash'  => Hashids::encode($date->id)
        ]);

        event(new OnlineReservationCreated($date));

        return response()->json($response);
    }

    public function makeBookingNew(Request $request)
    {
        $lang = $request->get('lang');
        $payment_preprocessor = $request->get('payment_preprocessor');
        $deposit_amount = $request->get('deposit_amount');
        $bill_deposit_amount = $request->get('bill_deposit_amount');
        $total_price = $request->get('total_price');
        $bill_avgTotal = $request->get('bill_avgTotal');

        $phone = intval($request->get('phone'));
        $name  = $request->get('name');
        $email = $request->get('email');
        $pinNumber = rand(1000, 9999);
        $address = $request->get('address') . ' - ' . $request->get('city') . ' - ' . $request->get('country');
        $matchThese = array('team_id' => \View::getShared()['currentTeamId'], 'phone' => $phone);
        $customer = Customer::updateOrCreate(
            $matchThese,
            ['team_id' => \View::getShared()['currentTeamId'], 'token' => $pinNumber, 'name' => $name,  'email' => $email,  'phone' => $phone,  'address' => $address]
        );
        $copa = [];
        $units = UnitCategory::find($request->get('category_id'))->units;
        if ($units) {
            $notAvailableDates = $this->getAvailabilityForBooking($request->get('category_id'), $request->get('check_in'), $request->get('check_out'));
            if (!in_array(Carbon::parse($request->get('check_in'))->format('Y-m-d'), $notAvailableDates)) {
                foreach ($units as $unit) {
                    // $hasIntersectionWorkable = $unit->has_intersection_check(Carbon::parse($request->get('check_in')) , Carbon::parse($request->get('check_out')));
                    $hasIntersectionWorkable = checkIfUnitHasReservation($unit->id, Carbon::parse($request->get('check_in')));
                    if (!$hasIntersectionWorkable) {

                        $source = Source::query()->where('name->en', 'website')->where('team_id', \View::getShared()['currentTeamId'])->first();
                        if (!$source) {
                            $source =  Source::create(['name' => ['en' => 'website', 'ar' => 'الموقع الإليكترونى'], 'team_id' => \View::getShared()['currentTeamId'], 'deleteable' => 0]);
                        }
                        $team = Team::with('owner')->find(\View::getShared()['currentTeamId']);
                        $reservation = new Reservation();
                        $reservation->team_id = \View::getShared()['currentTeamId'];
                        $reservation->unit_id = $unit->id;
                        $reservation->source_id =  $source->id;
                        $reservation->rent_type = 1;
                        $reservation->customer_id = $customer->id;
                        $reservation->date_in = Carbon::parse($request->get('check_in'))->format('Y-m-d');
                        $reservation->date_out = Carbon::parse($request->get('check_out'))->format('Y-m-d');
                        $reservation->is_online = 1;
                        $reservation->status = $payment_preprocessor == 'fandaqah' ? 'awaiting-confirmation' : 'awaiting-payment';
                        $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
                        // $reservation->created_by = \View::getShared()['currentTeamId'];
                        // $reservation->created_by = $team->owner->id;



                        $x = $request->get('total_price');
                        $e = $team->ewa() ? $team->ewa() / 100 : 0;
                        $v = $team->vat() ? $team->vat() / 100 : 0;
                        $t = $team->ttx() ? $team->ttx() / 100 : 0;
                        $y = $x / (1 + $e + $t + $v + ($v * $e));

                        $total_ewa = $y * $e;
                        $total_vat = ($y + $total_ewa) * $v;
                        $total_ttx = $y * $t;
                        $reservation->total_price = $x;
                        $reservation->sub_total = $y;
                        $reservation->vat_total = $total_vat;
                        $reservation->ewa_total = $total_ewa;
                        $reservation->ttx_total = $total_ttx;

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
                                $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $reservation->team_id)->value('value');
                                $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

                                $data = [
                                    'to' => $reservation->customer->email,
                                    'reply_to' => $hotelEmail,
                                    'subject' => __('Awaiting Confirmation'),
                                    'html' => view('email.customer.awaiting-confirmation-reservation')->with(['reservation' => $reservation])->render(),
                                ];

                                $send = sendMailUsingMailMicroservice($data);
                                // Mail::to($reservation->customer->email)->send(new AwaitingConfirmationReservationMail($reservation));
                            }

                            $response = [
                                'status' => 'booked',
                                'payment_preprocessor' => $payment_preprocessor
                            ];

                            if ($payment_preprocessor == 'sure-bills') {
                                $billInformation = $this->generateBill($reservation, $bill_deposit_amount, $bill_avgTotal, $lang);
                                if ($billInformation && $billInformation['bill_id']) {
                                    $reservation->bill_ref_id = $billInformation['bill_id'];
                                    $reservation->save();
                                }

                                $response['pay_url'] = $billInformation['pay_url'];
                            } else {
                                $response['reservation'] = $reservation;
                            }

                            event(new ReservationCreated($reservation, false));


                            return response()->json($response);
                        }
                    }
                }
            }

            return response()->json(['status' => 'no_units_to_book', 'customer_data' => new CustomerResource($customer)]);
        }

        return response()->json(['status' => 'no_units_to_book', 'customer_data' => new CustomerResource($customer)]);
    }

    /**
     * Generat our sweet Bill
     * @param $reservation
     * @param $deposit_amount
     * @param $total_price
     * @return mixed
     */
    function generateBill($reservation, $deposit_amount, $total_price, $lang)
    {
        $bills_client_id = $reservation->team->sure_bills_client_id;
        $bills_secret =  $reservation->team->sure_bills_secret;
        $data = [
            'customer_name' => $reservation->customer->name,
            'customer_email' => $reservation->customer->email,
            'customer_mobile' => substr($reservation->customer->phone, 3),
            'due_date' => Carbon::now()->format('d-m-Y'),
            'expiry_date' =>  0,
            'expiry_hours' =>  0,
            'expiry_minutes' => $reservation->team->websiteSetting->time_payment_completed ?? 10,
            'send_email' =>  false,
            'add_tax' =>  false,
            'tax_value' => false,
            'add_discount' =>  false,
            'discount_type' =>  'fixed',
            'discount_value' =>  null,
            'reference_id' => Hashids::encode($reservation->id),
            'application_id' => $bills_client_id,
            'application_secret' => $bills_secret
        ];


        $data['items'][] = [
            // 'name' => __('Reservation For :nights nights' , ['nights' => $reservation->nights] , $lang) . ' </br> ' . __('For Unit :unit' , ['unit' => $reservation->unit->unit_number . ' - ' . $reservation->unit->getTranslation('name', $lang) ] , $lang)  . ' </br> ' . __('In Hotel :name' , ['name' => $reservation->team->name] , $lang),
            'name' => __('Reservation For :nights nights', ['nights' => $reservation->nights], $lang) . ' </br> ' . __('From :from - To :to', ['from' =>  Carbon::parse($reservation->date_in)->format('Y/m/d'), 'to' => Carbon::parse($reservation->date_out)->format('Y/m/d')], $lang)  . ' </br> ' . __('For Unit :unit', ['unit' => $reservation->unit->getTranslation('name', $lang)], $lang)  . ' </br> ' . $reservation->team->name,
            'price' => $deposit_amount ?? $total_price,
            'quantity' => 1,
        ];


        $client = new Client(['base_uri' => config('app.bills_url')]);
        $engine = $client->post('/api/v1/bills/create', [
            'form_params' => $data
        ]);


        $payload = json_decode($engine->getBody()->getContents(), true);
        return $payload['data'];
    }


    public function getOnlineReservation(Request $request)
    {
        return new OnlineReservationResource(Reservation::find(Hashids::decode($request->get('id'))[0]));
    }

    public function getCities(Request $request)
    {
        $cities = CitiesResource::collection(Team::find(\View::getShared()['currentTeamId'])->attachedHotels->pluck('city_id')->unique()->filter()->map(function ($id) {
            return City::find($id);
        }));
        return response()->json($cities, 200);
    }

    public function getAttachedHotels(Request $request, $city_id)
    {
        $hotelsPerCity = RelatedHotelsResource::collection(Team::find(\View::getShared()['currentTeamId'])->attachedHotels->where('city_id', (int) $city_id));
        return response()->json($hotelsPerCity, 200);
    }

    public function getIndexUnitCategoryDetails(Request $request)
    {

        return new FeaturedUnitCategoryFullResource(UnitCategory::find(Hashids::decode($request->get('id'))[0]));
    }

    private function handlePinCode($mobile_number, $team, $locale, $type = null)
    {

        $pinNumber = rand(1000, 9999);
        $message = $this->formatSmsMessage($pinNumber, $locale);
        if (app()->environment() === 'production') {
            if ($type == 'yallabnb') {
                $response = sendSms($team->id, $message, $mobile_number, true, true);
            } else {
                $response = sendSms($team->id, $message, $mobile_number, true);
            }

            if ($response['status'] == 1) {
                $matchThese = array('team_id' => $team->id, 'phone' => $mobile_number);
                $customer = Customer::updateOrCreate(
                    $matchThese,
                    ['team_id' => $team->id, 'token' => $pinNumber, 'phone' => $mobile_number]
                );
                return response()->json(['status' => 'success', 'customer' => new CustomerResource($customer)]);
            } else {
                event(new SmsIntegrationDownEvent($response));
                return response()->json(['status' => 'error', 'code' => 500]);
            }
        } else {

            $matchThese = array('team_id' => $team->id, 'phone' => $mobile_number);
            $customer = Customer::updateOrCreate(
                $matchThese,
                ['team_id' => $team->id, 'token' => 1234, 'phone' => $mobile_number]
            );
            return response()->json(['status' => 'success',  'customer' => new CustomerResource($customer)]);
        }
    }

    public function getTokenForCustomerWebsiteClient(Request $request)
    {


        $team = Team::find(\View::getShared()['currentTeamId']);
        $mobile_number = intval($request->get('mobile_number'));
        $locale = $request->get('locale');


        return $this->handlePinCode($mobile_number, $team, $locale);

        // sendSms(\View::getShared()['currentTeamId'],)

        /**
         * If integration found we will use it
         * We must also check for available points
         * if no points available we will use fandaqah default unifonic account
         */

        if ($team->integrationUnifonic) {
            // tenant has integration
            $integrationSettings = json_decode($team->integrationUnifonic->values, true);

            if (isset($integrationSettings['appSid'])) {

                // we have integration with appSid key from unifonic platform
                if ($request->get('type') == 'yallabnb') {
                    \Config::set('services.unifonic.app_id', config('app.yallabnb_unifonic'));
                } else {
                    \Config::set('services.unifonic.app_id', $integrationSettings['appSid']);
                }

                $client = new Client(['base_uri' => 'https://api.unifonic.com/rest/']);
                $params['headers'] = ['Content-Type' => 'application/json'];
                $response = $client->post('Account/GetBalance?AppSid=' . \Config::get('services.unifonic.app_id'));
                $payload = json_decode($response->getBody()->getContents(), true);


                if (!empty($payload['data']) && (int) $payload['data']['remainingPoints']['points_count'] > 0) {
                    return $this->handlePinCode($mobile_number, $team, $locale, $request->get('type'));
                } else {


                    // we will use fandaqh default unifonic account
                    if ($request->get('type') == 'yallabnb') {
                        \Config::set('services.unifonic.app_id', config('app.yallabnb_unifonic'));
                    } else {
                        \Config::set('services.unifonic.app_id', config('app.fandaqah_unifonic'));
                    }


                    $client = new Client(['base_uri' => 'https://api.unifonic.com/rest/']);
                    $params['headers'] = ['Content-Type' => 'application/json'];
                    $response = $client->post('Account/GetBalance?AppSid=' . \Config::get('services.unifonic.app_id'));
                    $payload = json_decode($response->getBody()->getContents(), true);

                    if ((int) $payload['data']['remainingPoints']['points_count'] > 0) {
                        return $this->handlePinCode($mobile_number, $team, $locale, $request->get('type'));
                    } else {
                        return response()->json(['status' => 'error', 'msg' =>  __('No Sufficient Points From SMS Provider , We are working hard to fix this problem')]);
                    }
                }
            }
        } else {

            // there no integration with unifonid , so we will use fandaqah default unifonic account
            if ($request->get('type') == 'yallabnb') {
                \Config::set('services.unifonic.app_id', config('app.yallabnb_unifonic'));
            } else {
                \Config::set('services.unifonic.app_id', config('app.fandaqah_unifonic'));
            }

            $client = new Client(['base_uri' => 'https://api.unifonic.com/rest/']);
            $params['headers'] = ['Content-Type' => 'application/json'];
            $response = $client->post('Account/GetBalance?AppSid=' . \Config::get('services.unifonic.app_id'));
            $payload = json_decode($response->getBody()->getContents(), true);

            return $this->handlePinCode($mobile_number, $team, $locale, $request->get('type'));

            if ((int) $payload['data']['remainingPoints']['points_count'] > 0) {
                return $this->handlePinCode($mobile_number, $team, $locale, $request->get('type'));
            } else {
                return response()->json(['status' => 'error', 'msg' =>  __('No Sufficient Points From SMS Provider , We are working hard to fix this problem')]);
            }
        }
        return response()->json($team->integrationUnifonic);
    }

    public function getPages()
    {
        return PagesResource::collection(WebsitePage::whereNull('deleted_at')->whereStatus(1)->whereTeamId(\View::getShared()['currentTeamId'])->orderBy('order', 'asc')->get());
    }

    public function confirmOnlineReservation(Request $request)
    {
        $id = $request->get('id');
        $bill_id = $request->get('bill_id');
        $online_reservation = Reservation::find(Hashids::decode($id)[0]);

        // new guzzle client to send a pre-check to sure bills to get bill information
        // env key is used called BILLS_URL for the base sure bills end point url
        $team = Team::find(\View::getShared()['currentTeamId']);
        $client = new Client(['base_uri' => config('app.bills_url')]);
        $params = [
            'query' => [
                'application_id' => $team->sure_bills_client_id,
                'application_secret' => $team->sure_bills_secret,
            ]
        ];
        $engine = $client->get('/api/v1/bills/' . $bill_id, $params);

        // the payload coming from sure bills end point
        $payload = json_decode($engine->getBody()->getContents(), true);

        if ($payload['data']['status'] == 'paid') {
            return  $this->confirmReservation($online_reservation, $payload);
        }

        return \response()->json(['status' => 'failed']);
    }

    public function cancelOnlineReservation(Request $request)
    {
        $id = $request->get('id');
        $online_reservation = Reservation::find(Hashids::decode($id)[0]);

        $online_reservation->cancel();
        $online_reservation->cancelBill();

        return response()->json([
            'status' => 'canceled',
            'reservation' => $online_reservation->load('customer', 'unit.unit_category'),
        ]);
    }

    private function confirmReservation(Reservation $reservation, $payload)
    {

        /**
         * Check if there is a billed online transaction then stop the execution of the confirmReservation
         */
        $billedOnlineTransactionCheck = Transaction::where('payable_id', $reservation->id)
            ->where('payable_type', 'App\\Reservation')
            ->where('is_public', 1)
            ->whereNotNull('bill_payload')
            ->whereNull('deleted_at')
            ->exists();

        if ($reservation->status == 'confirmed' || $billedOnlineTransactionCheck) {
            return \response()->json([
                'status' => 'success',
                'online_reservation_id' => $reservation->id,
                'online_reservation_id_hashed' => Hashids::encode($reservation->id)
            ]);
        }

        $reservation->status = 'confirmed';
        $reservation->save();


        $depositMeta = [
            'date' => date('Y-m-d h:i', time()),
            'from' => $reservation->customer->name,
            'note' =>  null,
            'type' => 2,
            'category' => 'reservation',
            'employee' => '',
            'reference' => null,
            'statement' => 'Billed Online',
            'payment_type' => $payload['data']['payment_method']

        ];


        $transaction = $reservation->depositFloat($payload['data']['total'], $depositMeta, true, true);
        $reservation->wallet->refreshBalance();
        $transaction->bill_payload = $payload['data'];
        $reservation->number = $reservation->team->counter->reservation_num;
        $transaction->number = $reservation->team->counter->receipt_num;
        $transaction->save();
        $reservation->save();

        $counter = $reservation->team->counter;
        $counter->last_reservation_number = $counter->reservation_num;
        $counter->last_receipt_number = $counter->receipt_num;
        $counter->save();

        event(new OnlineReservationConfirmed($reservation));
        event(new ReservationCreated($reservation, true));


        return \response()->json([
            'status' => 'success',
            'online_reservation_id' => $reservation->id,
            'online_reservation_id_hashed' => Hashids::encode($reservation->id)
        ]);
    }
    private function formatSmsMessage($pin_number, $locale)
    {
        $message = __('verification code : ', [], $locale) . $pin_number;
        $message .= PHP_EOL;
        return $message;
    }

    public function getCustomersReviews()
    {
        // $unit_ids = UnitCategory::find(Hashids::decode($hash)[0])->units->pluck('id');
        // $ratings_ids = Reservation::has('rating')->where('team_id' , \View::getShared()['currentTeamId'])->whereIn('unit_id' , $unit_ids)->pluck('rating_id');
        // $query = Rating::query();
        // $query->whereIn('id' , $ratings_ids);
        // $query->where('status' , 1);
        // $query->orderByDesc('created_at');
        $ratings = Rating::with('reservation', 'reservation.unit')
            ->where('team_id', \View::getShared()['currentTeamId'])
            ->where('status', 1)
            // ->orderByDesc('overall_rating')
            ->orderByDesc('created_at')
            ->get();
        return WebsiteRatingResource::collection($ratings)->additional(['overall' => $this->getOverallRatings($ratings)]);
    }

    function getOverallRatings($ratings)
    {
        $q_one_factor = 0;
        $q_two_factor = 0;
        $q_three_factor = 0;
        $q_four_factor = 0;
        $q_five_factor = 0;
        $q_six_factor = 0;
        if (count($ratings)) {
            foreach ($ratings as $rating) {
                if ($rating->q_one > 0) {
                    $q_one_factor += 10;
                }
                if ($rating->q_two > 0) {
                    $q_two_factor += 10;
                }
                if ($rating->q_three > 0) {
                    $q_three_factor += 10;
                }
                if ($rating->q_four > 0) {
                    $q_four_factor += 10;
                }
                if ($rating->q_five > 0) {
                    $q_five_factor += 10;
                }
                if ($rating->q_six > 0) {
                    $q_six_factor += 10;
                }
            }
        }

        if (!$q_one_factor) {
            $q_one_factor = 10;
        }
        if (!$q_two_factor) {
            $q_two_factor = 10;
        }
        if (!$q_three_factor) {
            $q_three_factor = 10;
        }
        if (!$q_four_factor) {
            $q_four_factor = 10;
        }
        if (!$q_five_factor) {
            $q_five_factor = 10;
        }
        if (!$q_six_factor) {
            $q_six_factor = 10;
        }

        $data = [
            'q_one' =>  number_format(floatval(($ratings->sum('q_one') / $q_one_factor)) * 10, 2, '.', ''),
            'q_two' =>  number_format(floatval(($ratings->sum('q_two') / $q_two_factor)) * 10, 2, '.', ''),
            'q_three'   =>  number_format(floatval(($ratings->sum('q_three') / $q_three_factor)) * 10, 2, '.', ''),
            'q_four'    =>  number_format(floatval(($ratings->sum('q_four') / $q_four_factor)) * 10, 2, '.', ''),
            'q_five'    =>  number_format(floatval(($ratings->sum('q_five') / $q_five_factor)) * 10, 2, '.', ''),
            'q_six' =>  number_format(floatval(($ratings->sum('q_six') / $q_six_factor)) * 10, 2, '.', ''),
        ];

        $total = collect($ratings)->pluck('overall_rating')->sum() / count(collect($ratings)->where('overall_rating', '>', 0));
        $data['total'] = number_format($total, 2, '.', '');
        $data['count'] = $ratings->count();
        if ($total <= 5) {
            $label = 'Acceptable';
        } elseif ($total < 7) {
            $label = 'Good';
        } elseif ($total >= 7 and $total <= 8.5) {
            $label = 'Very good';
        } elseif ($total > 8.5) {
            $label = 'Excellent';
        }

        $data['label'] = $label;
        return $data;
    }

    public function yallaBnbUnitCategory($hash, $date_start, $date_end)
    {
        return new UnitCategoryWithSpecialPricesResource(UnitCategory::find(Hashids::decode($hash)[0]));
    }

    public function getAvailability(Request $request)
    {


        $cat_id = $request->get('cat_id');
        $start = $request->get('start');
        $end = $request->get('end');
        $team_id = \View::getShared()['currentTeamId'];

        $units = DB::table('units as u')
            ->select('u.id as uid', 'u.name as uname', 'u.unit_number as unum')
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
                    // $reservations =  DB::table('reservations as r')
                    //     ->leftJoin('customer as c','r.customer_id' , '=' ,'c.id')
                    //     ->leftJoin('highlights as h' , 'c.highlight_id' , '=' , 'h.id')
                    //     ->select( 'r.id as rid',
                    //         'r.date_in as rdi',
                    //         'r.date_out as rdo'
                    //     )
                    //     ->whereRaw('? between r.date_in and r.date_out', [$date->format('Y-m-d')])
                    //     ->where('r.date_out', '!=', $date->format('Y-m-d'))
                    //     ->whereNull('r.checked_out')
                    //     ->whereNull('r.deleted_at')
                    //     ->where('r.status' , '!=' , 'canceled')
                    //     ->where('r.team_id' , $team_id)
                    //     ->where('r.unit_id' , $unit->uid)
                    //     ->get();

                    $reservations = DB::table('reservations as r')
                        ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
                        ->leftJoin('highlights as h', 'c.highlight_id', '=', 'h.id')
                        ->select(
                            'r.id as rid',
                            'r.checked_in as rchi',
                            'r.date_in as rdi',
                            'r.date_out as rdo',
                            'r.status',
                            'c.name as cname',
                            'h.name as hlabel',
                            'h.color as hcolor'
                        )
                        ->whereRaw('? between r.date_in and r.date_out', [$date->format('Y-m-d')])
                        ->where('r.date_out', '!=', $date->format('Y-m-d'))
                        ->whereNull('r.checked_out')
                        ->whereNull('r.deleted_at')
                        ->whereNotIn('r.status', ['timeout', 'canceled'])
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
                    'number' => $unit->unum,
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
                $notAvailabileDates[] = Carbon::parse($key)->format('Y/m/d');
            }
            $availability[Carbon::parse($key)->format('Y/m/d')] = array_sum($value) < $units_count ? 'available' : 'not_available';
        }

        $prices = $this->daysPrices($cat_id, $period);
        $final['notAvailableDates'] = $notAvailabileDates;
        $final['prices'] = $prices;

        return \response()->json($final, Response::HTTP_OK);
    }

    public function getAvailabilityForBooking($cat_id, $start, $end , $team_id = null)
    {
if($team_id == null){

    $team_id = \View::getShared()['currentTeamId'];
}
        $team = Team::find($team_id);
        $units = DB::table('units as u')
            ->select('u.id as uid', 'u.name as uname')
            ->whereNull('u.deleted_at')
            ->where('u.unit_category_id', $cat_id)
            ->where('u.team_id', $team_id)
            ->whereIn('u.status', [1, 2])
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

    protected function daysPrices($cat_id, $period)
    {

        $unitCategory = UnitCategory::find($cat_id);
        $prices = [];
        foreach ($period as $date) {
            $prices[$date->format('Y-m-d')] = number_format($unitCategory->dayPrice($date->format('l')), 2);
        }

        return $prices;
    }

    protected function formDay($date, $reservations, $unit)
    {
        $day['is_today'] = $date->isToday();
        $day['number'] = $date->format('Y-m-d');
        $day['reservations'] = count($reservations);
        return $day;
    }

    /**
     * Selected Team Target Settings
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function createFromStaah(Request $request , $commingresevation = null){
        // return "false";
        $request = $request->all();
        $status = $request['reservations'][0]['status'];
        if($status == 'modified'){
            $reservation = Reservation::where('channel_booking_id' , $request['reservations'][0]['channel_booking_id'])->first();
            $commingresevation = $reservation;
        }elseif($status == 'cancelled'){
            $reservation = Reservation::where('channel_booking_id' , $request['reservations'][0]['channel_booking_id'])->first();
            if($reservation != null){

                $reservation->status = 'canceled';
                $reservation->save();
                $response = [
                    'reservation_notif' => [
                        'reservation_notif_id' => [$request['reservations'][0]['reservation_notif_id']]
                    ]
                    ];


                    return response()->json($response);
            }


                }




        // $price = $request['reservations'][0]['rooms'][0]['price'];

        // $aftertax = 0;
        // // loop througp price and get the sum of priceafter tax
        // foreach ($price as $key => $value) {
        //     $aftertax += $value['priceaftertax'];
        // }
        // dd( $request['reservations'][0]['totalprice']);
        $aftertax = $request['reservations'][0]['totalprice'] + $request['reservations'][0]['totaltax'];
        $team_id = $request['reservations'][0]['hotel_id'];
        // get from tax and ewa from team settings
        $tax = (getVatPercentageForUnit($team_id) === null) ? 0 : getVatPercentageForUnit($team_id) ;
        $ewa = (getEwaPercentageForUnit($team_id) === null) ? 0 : getEwaPercentageForUnit($team_id) ;
        $tax = $tax/100;
        $ewa = $ewa/100;
        $total_withot_vat = $aftertax/($tax+1);
        $total__without_ewa = $total_withot_vat/($ewa+1);
        $total_vat = $aftertax - $total_withot_vat ;
        $total_ewa = $total_withot_vat - $total__without_ewa ;
        $subtotal = $aftertax - $total_vat - $total_ewa;
        $total_price = $aftertax;

        $phone = $request['reservations'][0]['customer']['telephone'];
        $name = $request['reservations'][0]['customer']['first_name'] . ' ' . $request['reservations'][0]['customer']['last_name'];
        $email = $request['reservations'][0]['customer']['email'];
        $address = $request['reservations'][0]['customer']['address'];
        $date_in =$request['reservations'][0]['rooms'][0]['arrival_date'];
        $date_out = $request['reservations'][0]['rooms'][0]['departure_date'];
        $source = $request['reservations'][0]['affiliation']['source'];
        // $bookingId = $request['bookingId'];
        // $cmBookingId = $request['cmBookingId'];
        $matchThese = array('team_id' =>$team_id, 'phone' => $phone);
    $customer = Customer::updateOrCreate(
        $matchThese,
        ['team_id' => $team_id,  'name' => $name,  'email' => $email,  'phone' => $phone,  'address' => $address]
    );
        $unitCategory = $request['reservations'][0]['rooms'][0]['id'];
        // loop in  $request['rooms]
if($unitCategory != null){


    $units = UnitCategory::find($unitCategory)->units;
}else{
    $unitCategory = UnitCategory::where('team_id' , $team_id)->first();
    $units = UnitCategory::find($unitCategory->id)->units;
}




    if ($units) {

        $notAvailableDates = $this->getAvailabilityForBooking($unitCategory, $date_in, $date_out , $team_id);
        if (!in_array(Carbon::parse($date_in)->format('Y-m-d'), $notAvailableDates)) {
            $unit = null;
            foreach ($units as $unitx) {
                // $hasIntersectionWorkable = $unit->has_intersection_check(Carbon::parse($request->get('check_in')) , Carbon::parse($request->get('check_out')));
                $hasIntersectionWorkable = checkIfUnitHasReservation($unitx->id, Carbon::parse($date_in));
                if (!$hasIntersectionWorkable) {
                    $unit = $unitx;
                }
            }
                    $source_query = Source::query()->where('name->en', $source)->where('team_id', $team_id)->first();
                    if (!$source_query) {
                        $source = Source::create([
                            'name' => ['en' => $source, 'ar' => $source],
                            'team_id' => $team_id,
                            'deleteable' => 0
                        ]);

                    }else{
                        $source = $source_query;
                    }

                    $team = Team::with('owner')->find($team_id);
                    if($commingresevation != null){
                        $reservation = $commingresevation;
                        $reservation->id = $commingresevation->id;
                        $unit = $commingresevation->unit;

                    }else{

                        $reservation = new Reservation();
                    }
                    if($status == 'cancelled'){
                        $reservation->status = 'canceled';
                    }
                    else{
                        $reservation->status ='awaiting-payment';

                    }
                    $reservation->team_id = $team_id;
                    $reservation->unit_id = $unit->id;
                    $reservation->reservation_notif_id = $request['reservations'][0]['reservation_notif_id'];
                    $reservation->channel_booking_id = $request['reservations'][0]['channel_booking_id'];
                    $reservation->source_id =  $source->id;
                    $reservation->rent_type = 1;
                    $reservation->customer_id = $customer->id;
                    $reservation->date_in = Carbon::parse($date_in)->format('Y-m-d');
                    $reservation->date_out = Carbon::parse($date_out)->format('Y-m-d');
                    $reservation->is_online = 1;
                    $reservation->action_type = Reservation::ACTION_RESERVATION_AWAITING_CONFIRMATION;
                    $reservation->total_price = $total_price;
                    $reservation->sub_total = $subtotal;
                    $reservation->vat_total = $total_vat;
                    $reservation->ewa_total = $total_ewa;
                    $reservation->ttx_total = 0;
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


                        $response['reservation'] = $reservation;


                    event(new ReservationCreated($reservation, false));



                    }
                    $response = [
                        'reservation_notif' => [
                            'reservation_notif_id' => [$request['reservations'][0]['reservation_notif_id']]
                        ]
                        ];


                        return response()->json($response);

    }

}
}
public function updateFromStaah(Request $request){
    return response()->json(['status' => 'success', 'reservation' => 'reservation updated successfully']);
}

public function cancelFromStaah(Request $request){
    return response()->json(['status' => 'success', 'reservation' => 'reservation canceled successfully']);
}

    public function getSettingsForSelectedTeam(Request $request)
    {
        $team = Team::find(\View::getShared()['currentTeamId']);
        $general_settings = [
            'ewa' => $team->ewa() != "" ? (float) $team->ewa() : 0,
            'vat' => $team->vat() != "" ? (float) $team->vat() : 0,
            'ttx' => $team->ttx() != "" ? (float) $team->ttx() : 0
        ];
        $team_settings = [
            "name" => $team->name,
            "sure_bills_client_id" => $team->sure_bills_client_id,
            "sure_bills_secret" => $team->sure_bills_secret,
            "sure_bills_webhook_secret" => $team->sure_bills_webhook_secret,
            "payment_preprocessor" => $team->payment_preprocessor
        ];
        $website_settings = $team->websiteSetting['deposit_percentage'];
        return \response()->json(array_merge(['deposit_percentage' => $website_settings, "terms" => $team->websiteSetting['cancellation_policy'], 'general_settings' => $general_settings, 'team_settings' => $team_settings]));
    }
}
