<?php

namespace SureLab\Calender\Http\Controllers;

use App\Team;
use App\Unit;
use App\Offer;
use App\Wallet;
use App\Company;
use App\Country;
use App\Customer;
use App\Occupied;
use Carbon\Carbon;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\SpecialPrice;
use App\UnitCategory;
use App\UnitCleaning;
use App\UnitMaintenance;
use Carbon\CarbonPeriod;
use App\ServicesCategory;
use App\Handlers\Settings;
use App\Events\UnitUpdated;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\ReservationServiceMapper;
use App\Events\ReservationCreated;
use App\Services\CustomPagination;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\GroupReservationBalanceMapper;
use App\Http\Resources\CountryResource;
use App\Http\Resources\Index\UnitIndexResource;
use Surelab\Settings\ValueObjects\SettingRegister;
use App\Notifications\RoomNeedsCleaningNotification;
use App\Http\Resources\Index\ReservationIndexResource;
use App\Http\Resources\Index\ReservationIndexCustomResource;
use App\Http\Resources\Index\ReservationIndexResourceAlfajr;

class UnitController extends Controller
{
    public function status(Request $request)
    {
        $unit = Unit::find($request->get('unit'));
        switch ($request->get('type')) {
            case 'cleaning':
                $unit->status = Unit::STATUS_UNDER_CLEANING;
                UnitCleaning::create(['unit_id' =>  $unit->id, 'start_at' =>    new \DateTime()]);

                foreach ($unit->team->users as $user) {

                    if ($user->hasDeviceToken()) {
                        $user->notify(new RoomNeedsCleaningNotification($unit->name));
                    }
                }
                break;

            case 'maintenance':
                $unit->status = Unit::STATUS_UNDER_MAINTENANCE;
                //get maintenance type
                $maintenance_sub_type = $request->get('sub_type');
                $maintenance_expected_date_time = $request->get('expected_date_time');
                $maintenance_note = $request->get('note');
                UnitMaintenance::create(['unit_id' =>  $unit->id,
                                        'start_at' =>    new \DateTime(),
                                        'action_id' => $maintenance_sub_type,
                                        'expected_at' => $maintenance_expected_date_time,
                                        'note' => $maintenance_note]);


                break;
            case 'enabled':
                switch ($unit->status) {
                    case Unit::STATUS_UNDER_CLEANING:
                        $unit->cleanings()->whereNull(['completed_by', 'completed_at'])->update([
                            'completed_at'  =>  new \DateTime(),
                            'completed_by'  =>  auth()->user()->id
                        ]);
                        break;
                    case Unit::STATUS_UNDER_MAINTENANCE:
                        $unit->maintenances()->whereNull(['completed_by', 'completed_at'])->update([
                            'completed_at'  =>  new \DateTime(),
                            'completed_by'  =>  auth()->user()->id
                        ]);
                        break;
                }
                $unit->status = Unit::STATUS_ENABLED;
                break;
        }
        $unit->save();
        $team_id=auth()->user()->current_team_id;
        $team=Team::find($team_id);
        if($team->enable_aiosell){
            $this->updateAiosellInventoryOnStatus($unit->id);
        }
        if($team->mytravel_hotel_id){
            $mytravel = $this->updateMytravelInventoryOnStatus($unit->id);
    }
        event(new UnitUpdated($unit));
        //get updated unit
        $unit = Unit::find($request->get('unit'));
        return response()->json(['success'  =>  true, 'unit' => $unit]);
    }
    public function index(Request $request)
    {

        $date = $request->get('date');
        $day_end_time = Settings::get('day_end');
        $day_start_time = Settings::get('day_start');
        $current_date = Carbon::parse($date)->format('Y-m-d');
        $combinedDateEnd = date('Y-m-d H:i:s', strtotime("$current_date $day_end_time"));
        // $combinedDateStart = date('Y-m-d H:i:s', strtotime("$current_date $day_start_time"));
        $current_time = date('H:i:s');
        $dayName = Carbon::parse($date)->format('l');
        // get from teams table the value of enable_unite_pagination   and per_page
        $enable_unite_pagination = auth()->user()->current_team->enable_unite_pagination;

        $page_number =  $request->get('page') ;
        $next = null;
   // make merge betweeen date and day_end and compare it with current time
   $current_date_time = $date . ' ' . $current_time;
   $current_date_time = Carbon::parse($current_date_time)->format('Y-m-d H:i:s');
   $cuurent_day_end_time = $date.' '.$day_end_time;
   $current_day_end_time = Carbon::parse($cuurent_day_end_time)->format('Y-m-d H:i:s');
   $current_day_start_time = $date.' '.$day_start_time;
   $current_day_start_time = Carbon::parse($current_day_start_time)->format('Y-m-d H:i:s');

        $alphabet = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
        if(in_array(auth()->user()->current_team_id ,explode(',', env('ALPHABETICAL_TEAMS')))){
            $units = Unit::whereEnabled(1)
            ->when($request->get('cat_id') != null , function ($query) use($request) {
                $query->where('unit_category_id',$request->get('cat_id'));
            })
            ->whereNull('deleted_at')
            ->get();
        }else{
            if($request->get('unit_status')== 4 ){


                // get day_end from settings table and compare it with current time if current time > day_end then add 1 day to date
                if($current_date_time > $current_day_end_time){
                    $date2 = Carbon::parse($date)->addDay()->format('Y-m-d');
                }elseif($current_date_time < $current_day_start_time){
                    $date2 = Carbon::parse($date)->subDay()->format('Y-m-d');
                }else{
                    $date2 = $date;
                }
                $checked_in = Reservation::where('team_id' , auth()->user()->current_team_id)
                ->where('date_out' , '>=' , $date2)
                ->where('date_in' , '<=' , $date2)
                ->whereNotNull('checked_in')
                ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->whereNotIn('status' , ['timeout','canceled'])
                ->pluck('unit_id');
          // get from units table the units ids = checked_in
                $units = Unit::whereEnabled(1)
                ->when($request->get('cat_id') != null , function ($query) use($request) {
                    $query->where('unit_category_id',$request->get('cat_id'));
                })
                ->whereIn('id' , $checked_in)
                ->whereNull('deleted_at')
                ->get()
                ->sortBy(function ($i) use($alphabet) {
                    return trim(str_replace($alphabet,'', $i['unit_number']));
                });


            }elseif($request->get('unit_status')== 5){
                // dd($current_date_time , $current_day_end_time , $current_day_start_time ,$day_end_time , $day_start_time);
               // get now date and time and compare it with day_end and day_start
                $nowdate = Carbon::now()->format('Y-m-d H:i:s');


                if($current_date_time > $current_day_end_time){
                    $date_end = Carbon::parse($date)->addDay()->format('Y-m-d');
                }elseif($current_date_time < $current_day_end_time){
                    $date_end = $date;
                }

                if($current_date_time < $current_day_start_time){
                    $date_start = Carbon::parse($date)->subDay()->format('Y-m-d');

                }elseif($current_date_time > $current_day_start_time){
                    $date_start = $date;
                }




                // dd($date_start , $date_end);








                    // //  $current_date_time = $nowdate;
                    // if($nowdate > $current_day_end_time){
                    //     $date2 = Carbon::parse($date)->addDay()->format('Y-m-d');
                    //     $current_day_start_time = $date2.' '.$day_start_time;
                    //     // dd($nowdate < $current_day_start_time);

                    // if($nowdate < $current_day_start_time){
                    //     $date2 = Carbon::parse($date)->subDay()->format('Y-m-d');
                    //     // dd($date2);
                    // }
                    // }elseif($current_date_time < $current_day_start_time){
                    //     $date2 = Carbon::parse($date)->subDay()->format('Y-m-d');
                    // }else{
                    //     $date2 = $date;
                    // }
                    // // dd($date2);
                if($date != Carbon::today()->format('Y-m-d')){
                    $not_checked_in = Reservation::where('team_id' , auth()->user()->current_team_id)
                    ->where('date_out' , '>=' , $date_end)
                    ->where('date_in' , '<=' , $date_start)
                    ->where('date_out', '!=' , $date)
                    ->whereNull('checked_in')
                    ->whereNull('checked_out')
                    ->whereNull('deleted_at')
                    ->whereNotIn('status' , ['timeout','canceled'])
                    ->pluck('unit_id');
                    // get from units table the units ids = not_checked_in
                    $units = Unit::whereEnabled(1)
                    ->when($request->get('cat_id') != null , function ($query) use($request) {
                        $query->where('unit_category_id',$request->get('cat_id'));
                    })
                    ->whereIn('id' , $not_checked_in)
                    ->whereNull('deleted_at')
                    ->get()
                    ->sortBy(function ($i) use($alphabet) {
                        return trim(str_replace($alphabet,'', $i['unit_number']));
                    });
                }else{
                $not_checked_in = Reservation::where('team_id' , auth()->user()->current_team_id)
                ->where('date_out' , '>=' , $date_end)
                ->where('date_in' , '<=' , $date_start)
                ->whereNull('checked_in')
                ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->whereNotIn('status' , ['timeout','canceled'])
                ->pluck('unit_id');
                // get from units table the units ids = not_checked_in
                $units = Unit::whereEnabled(1)
                ->when($request->get('cat_id') != null , function ($query) use($request) {
                    $query->where('unit_category_id',$request->get('cat_id'));
                })
                ->whereIn('id' , $not_checked_in)
                ->whereNull('deleted_at')
                ->get()
                ->sortBy(function ($i) use($alphabet) {
                    return trim(str_replace($alphabet,'', $i['unit_number']));
                });
            }
            }else{
                $units = Unit::whereEnabled(1)
                ->when($request->get('cat_id') != null , function ($query) use($request) {
                    $query->where('unit_category_id',$request->get('cat_id'));
                })
                ->when($request->get('unit_status') != null, function ($query) use ($request) {
                    $query->where('status', $request->get('unit_status'));
                })
                ->whereNull('deleted_at')
                ->get()
                ->sortBy(function ($i) use($alphabet) {
                    return trim(str_replace($alphabet,'', $i['unit_number']));
                });
            }
        }


        $units_ids = $units->pluck('id');
        if($page_number != null && $enable_unite_pagination == 1){
            // get per_page from teams table
            $per_page = auth()->user()->current_team->per_page;
            $no_pages = ceil(count($units_ids) / $per_page);
            $units = $units->forPage($page_number, $per_page  );
                $next = $no_pages;

        }


        $previousReservationsFormatter = array();


        $previousReservationsCollection = Reservation::with(['wallet','customer', 'company' , 'group_reservation'])
            ->where('team_id' , auth()->user()->current_team_id)
            // ->when($date != Carbon::today()->format('Y-m-d') , function ($query) use($date){
            // })
            ->whereRaw('? between date_in and date_out', [$date])
            ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->whereNotIn('status' , ['timeout','canceled'])
            ->where('date_out_time', '<=', $combinedDateEnd)
            ->whereIn('unit_id' , $units_ids)
            ->orderBy('created_at' , 'desc')
            ->get();

        if(count($previousReservationsCollection)){
            foreach ($previousReservationsCollection as $reservation) {
                $previousReservationsFormatter[$reservation->unit_id] = $reservation;
            }
        }

        $checkedInReservationsFormatter = array();


        $checkedInReservationsCollection = Reservation::with(['wallet','customer' , 'company' ,'group_reservation'])
            ->where('team_id' , auth()->user()->current_team_id)
            ->where('date_out' , '<=' , $date)
            ->whereNotNull('checked_in')
            ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->whereNotIn('status' , ['timeout','canceled'])
            ->whereIn('unit_id' , $units_ids)
            ->orderBy('created_at' , 'desc')
            ->get();


        if(count($checkedInReservationsCollection)){
            foreach ($checkedInReservationsCollection as $reservation) {
                $checkedInReservationsFormatter[$reservation->unit_id] = $reservation;
            }
        }


        $currentReservationsFormatter = array();

        $currentReservationsCollection = Reservation::with(['wallet','customer', 'company' ,'group_reservation'])
        ->where('team_id' , auth()->user()->current_team_id)
        ->whereRaw('? between date_in and date_out', [$date])
        ->whereNull('checked_out')
        ->whereNull('deleted_at')
        ->where('date_out', '!=', $date)
        ->whereNotIn('status' , ['timeout','canceled'])
        ->whereIn('unit_id' , $units_ids)
        ->orderBy('created_at' , 'desc')
        ->get();


        if(count($currentReservationsCollection)){
            foreach ($currentReservationsCollection as $reservation) {
                // $reservation->wallet->refreshBalance();
                $currentReservationsFormatter[$reservation->unit_id] = $reservation;
            }
        }


        $data = [];
        $today =  Carbon::today()->format('Y-m-d');
        $currentTime =  Carbon::now()->format('H:i');
        foreach ($units as $unit){

            $actorObj = new \stdClass();
            $actorObj->id = $unit->id;
            $actorObj->name = $unit->name;
            $actorObj->unit_number = $unit->unit_number;
            $actorObj->status = $unit->status;
            // $actorObj->day_price = number_format($unit->dayPrice($dayName),2);
            $unit_housing_date = Carbon::parse($date)->format('Y-m-d');
            $actorObj->day_price = number_format($unit->dayPriceFromSpecialPriceIfFound($unit_housing_date),2);

            $actorObj->date = $date;

            $actorObj->previous_reservation = isset($previousReservationsFormatter[$unit->id]) ? new ReservationIndexResource($previousReservationsFormatter[$unit->id]) : null;
            $actorObj->current_reservation = isset($currentReservationsFormatter[$unit->id]) ? new ReservationIndexResource($currentReservationsFormatter[$unit->id]) : null;
            $actorObj->legacy_checkedin_reservation = isset($checkedInReservationsFormatter[$unit->id]) ? new ReservationIndexResource($checkedInReservationsFormatter[$unit->id]) : null;

            // $actorObj->reservations_count = $unit->reservations_count;
            $actorObj->currentTime = $currentTime;
            $actorObj->today = $today;
            if($unit->status == Unit::STATUS_UNDER_MAINTENANCE) {
                $actorObj->maintenance_info = $unit->maintenance_info;
            }
            $data[] = $actorObj;
        }

        return response()->json(['items' => $data , 'next' => $next]);

    }


    protected function getReservationBasedOnDate($unit, $date)
    {
        return Reservation::where(['status' => 'confirmed'])
            ->where('team_id', '!=', null)
            ->where('unit_id', $unit->id)
            ->betweenDate($date)
            ->whereNull('checked_out')
            ->where('date_out', '!=', $date)
            ->first();
    }

    public function calculations(Request $request)
    {

        $now = Carbon::now()->startOfDay();
        $date = Carbon::parse($request->get('date'));
        $available_units = 0;
        $occupied_percentage  = 0;
        $units = Unit::whereEnabled(true)->where('status', '!=', 0)->get();

        if ($date->gte($now)) {
            $gte = true;


//             $occupied_units = Reservation::whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
//                 ->whereDateBetween(Carbon::now()->startOfDay())
// //                ->where('status', '!=', 'canceled')
//                 ->whereNotIn('status', ['canceled' , 'timeout'])
//                 ->whereNull('checked_out')
//                 ->whereNotNull('checked_in')
//                 ->count();


            $occupied_units = DB::table('reservations')
                ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
                ->where('date_in', '<=', Carbon::now()->startOfDay())
                ->where('date_out', '>', Carbon::now()->startOfDay())
                ->whereNotIn('status', ['canceled', 'timeout'])
                ->whereNull('checked_out')
                ->whereNotNull('checked_in')
                ->count();


            // $booked_units = Reservation::whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
            //     ->whereDateBetween(Carbon::now()->startOfDay())
            //     ->whereNotIn('status', ['canceled' , 'timeout'])
            //     ->whereNull('checked_out')
            //     ->whereNull('checked_in')
            //     ->count();

            $booked_units = DB::table('reservations')
                ->whereIn('unit_id', $units->where('status', 1)->pluck('id')->toArray())
                ->where('date_in', '<=', Carbon::now()->startOfDay())
                ->where('date_out', '>', Carbon::now()->startOfDay())
                ->whereNotIn('status', ['canceled', 'timeout'])
                ->whereNull('checked_out')
                ->whereNull('checked_in')
                ->count();

            // $under_cleaning_units = $units->where('status', 2)->count();
            
            $under_cleaning_units = Unit::whereEnabled(true)
            ->where('status', 2)
            ->count();

            // $under_maintenace_units = $units->where('status', 3)->count();

            $under_maintenace_units = Unit::whereEnabled(true)
            ->where('status', 3)
            ->count();

            $occupied_percentage = ($units->count()) ? round(($occupied_units + $booked_units) / $units->count() * 100, 2) : 0;
            $available_units = count($units) - ($occupied_units + $booked_units + $under_cleaning_units + $under_maintenace_units);
        } else {
            $gte = false;
            // $occ = Occupied::whereDate('created_at', $date)->first();
            // if ($occ) {
            //     $occupied_percentage = number_format((($occ->occupied + $occ->booked) / $occ->units_count) * 100, 2) ?? false;
            //     $available_units = $occ->available;
            // }

            $occ = Occupied::whereDate('created_at', $date)
                ->select('occupied', 'booked', 'units_count', 'available')
                ->first();

            if ($occ) {
                $occupied_percentage = $occ->units_count > 0 
                    ? number_format((($occ->occupied + $occ->booked) / $occ->units_count) * 100, 2)
                    : false;
                $available_units = $occ->available;
            }

        }

        // $safe_balance = cache()->rememberForever('safe_balance_' . auth()->user()->current_team->id, function() {
        //     return number_format($this->getTransactionsData('deposit')->total - $this->getTransactionsData('withdraw')->total , 2 , '.' , '');
        // });
        return response()->json(['gte' => $gte,  'occupied_percentage' => $occupied_percentage, 'available_units' => $available_units]);
    }

    public function getSafeBalance(Request $request)
    {
        # get the safe balance in unit housing screen
        $total_deposits = $this->getTransactionsData('deposit');
        $total_withdraws = $this->getTransactionsData('withdraw');

        return response()->json(['safe_balance' => number_format($total_deposits - $total_withdraws , 2 , '.' , '')]);
    }

    private function getTransactionsData($type)
    {

        // General holders
        // $cash_transactions = [];
        // $bank_transfer_transactions = [];
        // $mada_transactions = [];
        // $credit_card_transactions = [];
        $total = [];
        // fetch reservations ids
        $reservations_ids = Reservation::where('team_id', auth()->user()->current_team_id)->pluck('id')->toArray();


        $transactionsReservation = Transaction::with('wallet')
            ->where('amount', '!=' , 0)
            ->where('type' , '=' , $type)
            ->where('is_public' ,1)
            ->where('payable_type' , 'App\\Reservation')
            ->whereIn('payable_id', $reservations_ids)

            ->get();


        $transactionsTeam = Transaction::with('wallet')
            ->where('amount', '!=' , 0)
            ->where('type' , '=' , $type)
            ->where('is_public' ,1)
            ->where('payable_id', auth()->user()->current_team_id)
            ->where('payable_type' , 'App\\Team')
            ->get();

        $transactions = $transactionsTeam->merge($transactionsReservation);
        // // Fetch transactions statistics
        // $transactionsStatistics = Transaction::whereHasMorph('payable', Team::class, function ($t) use($type) {
        //         $t->where('payable_id', auth()->user()->current_team_id)->where('is_public' , 1)
        //             ->where('type' , $type);
        //     })
        //     ->orWhereHasMorph('payable', Reservation::class, function ($t) use ($reservations,$type) {
        //         $t->whereIn('payable_id', $reservations)->where('is_public' , 1)
        //             ->where('type' , $type);
        //     })
        //     ->with('wallet')
        //     ->orderByDesc('number')
        //     ->get();


        if ($transactions) {
            foreach ($transactions as $transaction) {

                // check if payment type key isset
                if (isset($transaction->meta['payment_type'])) {

                    $total [] = abs($transaction->amount / ($transaction->wallet->decimal_places == 3 ? 1000 : 100));
                }
            }

            return array_sum($total);
        }
    }


    protected function number_format_abs($number)
    {
        return number_format(floatval($number), 2);
    }

    public function get_units($date_start, $date_end, Request $request)
    {
        $date_start = Carbon::parse($date_start);
        $date_end = Carbon::parse($date_end);
        $rent_type = $request->get('rent_type');
        // setup
        if ($date_start != $date_end) {
            $date_end->subDay();
        }

        $currentPeriodAccordingToSelectedDates = CarbonPeriod::create($date_start, $date_end);
        $currentDatesHolder = [];
        foreach ($currentPeriodAccordingToSelectedDates as $date) {
            if (!in_array($date->format('Y-m-d'), $currentDatesHolder)) {
                $currentDatesHolder[] = $date->format('Y-m-d');
            }
        }

        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        // we will bypass the units that don't have month_price if the rent type selected was monthly
        if ($rent_type == 'monthly') {
            $units = Unit::whereEnabled(true)
                ->whereStatus(1)
                ->whereNotNull('month_price')
                ->where('month_price', '>', 0)
                ->whereNull('deleted_at')
                ->get();
        } else {
            $units = Unit::whereEnabled(true)
                ->whereStatus(1)
                ->whereNull('deleted_at')
                ->get();
        }

        $results = [];
        if(count($units)){
            foreach ($units as $unit) {
                $check_unit_has_reservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit, $date_start, $currentDatesHolder);
                if(!$check_unit_has_reservation){
                    $results['units'][] = [
                        'id' => $unit->id,
                        'unit_number' => $unit->unit_number,
                        'name' => $unit->name,
                        'has_reservation' => false,
                        'prices' => $unit->prices(),
                        'min_prices' => $unit->minPrices(),
                        'reservation' => [
                            'start_date' => $date_start->format('d-m-Y'),
                            'end_date' => $date_end->format('d-m-Y'),
                            'days' => $diff_days,
                            'nights' => $diff_nights,
                            'month' => ['count' => floor($diff_nights / 30), 'nights' => $diff_nights % 30],
                            'prices' => $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($date_end), $request->get('rent_type')),
                        ],
                        'reservations_date' => $unit->getReservationsDates(),
                        'online_reservations' => $unit->getOnlineReservations($date_start),
                    ];
                }

            }

        }

        if(isset($results['units'])){
            $keys = array_column($results['units'], 'unit_number');
        }

        if(isset($results['units']) && !in_array(auth()->user()->current_team_id ,explode(',', env('ALPHABETICAL_TEAMS')))){
            array_multisort($keys, SORT_DESC, $results['units']);
        }

        $results['utility'] = [
            'SCTH' => Settings::checkIntegration('SCTH', \Auth::user()->current_team_id),
            'SHMS' => Settings::checkIntegration('SHMS', \Auth::user()->current_team_id),
            'purpose_of_visit' => Customer::purposeOfVisit(),
            'purpose' => null,
            //            'nationalities' => CountryResource::collection(Country::all()),
            //            'id_types' => Customer::idTypes(),
            'customer_types' => Customer::customerTypes(),
        ];
        return $results;
    }
    /**
     * @todo this is an old version for update reservation rolled back as per islam rashad request
     *
     * @param [type] $id
     * @param [type] $date_start
     * @param [type] $date_end
     * @param Request $request
     * @return void
     */
    public function show($id, $date_start, $date_end, Request $request)
    {
        $unit = Unit::with('unit_category')->findOrFail($id);
        $date_start = Carbon::parse($date_start);
        $date_end = Carbon::parse($date_end);
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        $special_prices_array = [];
        $special_prices =  $unit->unit_category && $unit->unit_category->special_prices ? $unit->unit_category->special_prices : [] ;
    
        if(count($special_prices)){
            foreach ($special_prices as $special_price) {
                $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                foreach ($special_price_period as $special_price_date) {
                    // saftey in case special prices added with empty days prices 
                    if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                        $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                    } else {
                        $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                    }
                }
            }
        }
        $prices = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($date_end), $request->get('rent_type'),$special_prices_array);

        if ($request->has('reservation_id')) {
            // current reservation
            $reservation = Reservation::with('unit')->find($request->get('reservation_id'));

            if($id == $reservation->unit_id){
                $prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($date_start), Carbon::parse($date_end), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
            }else{
                 // id here is the new incoming unit id
                $firstPotentialReservationOnTheNewUnit = Reservation::where('unit_id' , $id)
                ->whereNull('deleted_at')
                ->whereNotNull('checked_in')
                ->whereNull('checked_out')
                ->whereStatus('confirmed')
                ->latest()
                ->first();

                if($firstPotentialReservationOnTheNewUnit && $firstPotentialReservationOnTheNewUnit->checked_in && $reservation->checked_in){
                    // here we can move checked-in reservation on another unit that already still has a checked-in reservation
                    return response()->json(['status' => 'new_unit_still_has_checked_in_reservation']);
                }
                if($request->get('transfer_with_same_price')){
                    $special_prices_array = [];
                    $special_prices = $reservation->unit->unit_category->special_prices;
                    if(count($special_prices)){
                        foreach ($special_prices as $special_price) {
                            $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                            foreach ($special_price_period as $special_price_date) {
                                // saftey in case special prices added with empty days prices 
                                if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                                    $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                                } else {
                                    $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                                }
                            }
                        }
                    }

                    if(count($special_prices_array)){
                        $prices = $unit->getDatesFromRangeWithOldPricesAndSpecialPrices(Carbon::parse($date_start), Carbon::parse($date_end), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'),$special_prices_array);
                    }else{
                        $prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($date_start), Carbon::parse($date_end), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
                    }

                }else{
                    $prices = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($date_end), $request->get('rent_type'));
                }
            }
            // if ($reservation->old_prices)
            //     $prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($date_start), Carbon::parse($date_end), $reservation->old_prices, $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
        }

        $result = [
            'id' => $unit->id,
            'unit_number' => $unit->unit_number,
            'name' => $unit->name,
            //            'SCTH' => Settings::checkIntegration('SCTH', $unit->team_id),
            //            'SHMS' => Settings::checkIntegration('SHMS', $unit->team_id),
            'purpose_of_visit' => Customer::purposeOfVisit(),
            'purpose' => null,
            'nationalities' => CountryResource::collection(Country::all()),
            'id_types' => Customer::idTypes(),
            'customer_types' => Customer::customerTypes(),
            'has_reservation' => count($unit->getReservations($date_start)),
            'prices' => $unit->prices(),
            'min_prices' => $unit->minPrices(),
            'reservation' => [
                'start_date' => $date_start->format('d-m-Y'),
                'end_date' => $date_end->format('d-m-Y'),
                'days' => $diff_days,
                'nights' => $diff_nights,
                'month' => ['count' => floor($diff_nights / 30), 'nights' => $diff_nights % 30],
                'prices' => $prices,
            ],
            'reservations_date' => $unit->getReservationsDates(),
            'online_reservations' => $unit->getOnlineReservations($date_start),
            'settings_day_start' => $unit->settings_day_start
        ];
        return $result;
    }

    function dayPrice($unit,$day){
        switch ($day) {
            case "Sunday":
                return $unit->sunday_day_price;
                break;
            case "Monday":
                return $unit->monday_day_price;
                break;
            case "Tuesday":
                return $unit->tuesday_day_price;
                break;
            case "Wednesday":
                return $unit->wednesday_day_price;
                break;
            case "Thursday":
                return $unit->thursday_day_price;
                break;
            case "Friday":
                return $unit->friday_day_price;
                break;
            case "Saturday":
                return $unit->saturday_day_price;
                break;
            default:
                echo null;
        }
    }
    // public function show($id, $date_start, $date_end, Request $request)
    // {
    //     $unit = Unit::findOrFail($id);

    //     $date_start = Carbon::parse($date_start);
    //     $date_end = Carbon::parse($date_end);
    //     $diff_days = $date_start->diffInDays($date_end);
    //     $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

    //     $prices = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($date_end), $request->get('rent_type'));

    //     if ($request->has('reservation_id')) {
    //         $reservation = Reservation::find($request->get('reservation_id'));
    //         if($id == $reservation->unit_id){
    //             $prices = $unit->getDatesFromRangeWithOldPrices(Carbon::parse($date_start), Carbon::parse($date_end), $reservation->old_prices , $reservation->prices['days'] , 'update' ,  $reservation->change_rate, $reservation->rent_type, $request->get('rent_type'));
    //             // dd($prices);
    //         }else{
    //             $prices = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($date_end), $request->get('rent_type'));
    //         }
    //     }
    //     $result = [
    //         'id' => $unit->id,
    //         'unit_number' => $unit->unit_number,
    //         'name' => $unit->name,
    //         //            'SCTH' => Settings::checkIntegration('SCTH', $unit->team_id),
    //         //            'SHMS' => Settings::checkIntegration('SHMS', $unit->team_id),
    //         'purpose_of_visit' => Customer::purposeOfVisit(),
    //         'purpose' => null,
    //         'nationalities' => CountryResource::collection(Country::all()),
    //         'id_types' => Customer::idTypes(),
    //         'customer_types' => Customer::customerTypes(),
    //         'has_reservation' => count($unit->getReservations($date_start)),
    //         'prices' => $unit->prices(),
    //         'min_prices' => $unit->minPrices(),
    //         'reservation' => [
    //             'start_date' => $date_start->format('d-m-Y'),
    //             'end_date' => $date_end->format('d-m-Y'),
    //             'days' => $diff_days,
    //             'nights' => $diff_nights,
    //             'month' => ['count' => floor($diff_nights / 30), 'nights' => $diff_nights % 30],
    //             'prices' => $prices,
    //         ],
    //         'reservations_date' => $unit->getReservationsDates(),
    //         'online_reservations' => $unit->getOnlineReservations($date_start),
    //         'settings_day_start' => $unit->settings_day_start
    //     ];
    //     return $result;
    // }
    public function commonSelectors()
    {

        return [
            'purpose_of_visit' => Customer::purposeOfVisit(),
            'purpose' => null,
            'nationalities' => CountryResource::collection(Country::orderBy(app()->getLocale() == 'ar' ? 'title->ar' : 'title->en')->get()),
            'id_types' => Customer::idTypes(),
            'customer_types' => Customer::customerTypes()
        ];
    }
    public function getTeamSettingDayStart(Request $request)
    {
        return response()->json(['day_start' => Settings::get('day_start') , 'transfer_with_same_price' => Settings::get('transferـcustomer_to_another_unit_with_the_same_price') , 'server_date' => Carbon::now()->format('Y-m-d')  ]);
    }
    public function selectors()
    {

        return response()->json([
            'nationalities' => CountryResource::collection(Country::orderBy(app()->getLocale() == 'ar' ? 'title->ar' : 'title->en')->get()),
            'id_types' => Customer::idTypes(),
            'customer_types' => Customer::customerTypes()
        ]);
    }
    public function counts()
    {
        $result = [];
        $result['units'] = Unit::whereEnabled(true)->count();
        $result['unitCategories'] = UnitCategory::count();
        return response()->json($result);
    }
    /**
     * @param $start
     * @param $end
     * @return \Illuminate\Http\JsonResponse
     */
    public function calenderUnits(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));

        $reservations = Reservation::whereBetween('date_out', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('status', '=', Reservation::STATUS_CONFIRMED)
            ->whereNull('checked_out')
            ->where('team_id', '=', auth()->user()->current_team_id)
            ->get();
        $period = CarbonPeriod::create($start, $end);
        $units = Unit::where('team_id', '=', auth()->user()->current_team_id)->whereEnabled(true)->count();

        $calender_events = [];
        /** @var Carbon $date */
        foreach ($period as $date) {
            $count = Reservation::countForDate($date);

            /** @var Reservation $reservation */
            foreach ($reservations as $reservation) {
                $d = $date;
                /** @var \DateInterval $diff */
                $diff = $d->diff($reservation->date_out);
                if (
                    !$diff->invert &&
                    $d->format('Y-m-d') != $reservation->date_in &&
                    $d->format('Y-m-d') > $reservation->date_in &&
                    $d->format('Y-m-d') != $reservation->date_out
                ) {
                    $count++;
                }
            }

            $count = $units - $count;
            if ($count > 0)
                $calender_events[] = [
                    'title' => $count . ' ' . __('Available'),
                    'start' => $date->format('Y-m-d'),
                ];
        }

        return response()->json($calender_events);
    }
    /**
     * @param $start
     * @param $end
     * @return \Illuminate\Http\JsonResponse
     */
    public function calenderUnitsReserved(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));

        $reservations = Reservation::whereBetween('date_out', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where('status', '=', Reservation::STATUS_CONFIRMED)
            ->whereNull('checked_out')
            ->where('team_id', '=', auth()->user()->current_team_id)
            ->get();

        $period = CarbonPeriod::create($start, $end);

        $calender_events = [];
        /** @var Carbon $date */
        foreach ($period as $date) {
            $count = Reservation::countForDate($date);
            /** @var Reservation $reservation */
            foreach ($reservations as $reservation) {
                $d = $date;
                /** @var \DateInterval $diff */
                $diff = $d->diff($reservation->date_out);
                if (
                    !$diff->invert &&
                    $d->format('Y-m-d') != $reservation->date_in &&
                    $d->format('Y-m-d') > $reservation->date_in &&
                    $d->format('Y-m-d') != $reservation->date_out
                ) {
                    $count++;
                }
            }
            if ($count > 0)
                $calender_events[] = [
                    'title' => $count . ' ' . __('Not Available'),
                    'start' => $date->format('Y-m-d'),
                ];
        }
        return response()->json($calender_events);
    }
    public function available(Request $request)
    {
        $date_in = Carbon::parse($request->date_in);
        $date_out = Carbon::parse($request->date_out);
        $now = Carbon::now()->startOfDay();

        $units = Unit::where('status', 1)->whereEnabled(true)->get();

        $not_available_ids = Reservation::whereIn('unit_id', $units->pluck('id')->toArray())
            ->whereDateBetweenDates($date_in, $date_out)
            ->where(['status' => 'confirmed'])
            ->whereNull('checked_out')
            ->pluck('unit_id')->unique()->toArray();
        $not_available_ids = array_diff($not_available_ids, $request->plus);

        $units = $units->whereNotIn('id', $not_available_ids);

        if ($date_in != $date_out) {
            $date_out->subDay();
        }

        $currentPeriodAccordingToSelectedDates = CarbonPeriod::create($date_in, $date_out);
        $currentDatesHolder = [];
        foreach ($currentPeriodAccordingToSelectedDates as $date) {
            if(!in_array($date->format('Y-m-d'),$currentDatesHolder)){
                $currentDatesHolder [] = $date->format('Y-m-d');
            }
        }

        $result = [];
        /** @var Unit $unit */
        foreach ($units as $unit) {
            $result[] = [
                'id' => $unit->id,
                'name' => $unit->name,
                'unit_number' => $unit->unit_number,
                'status' => $unit->status,
                'date' => $date_in->format('d-m-Y'),
                'day_name' => $date_in->format('l'),
                'prices' => [
                    'day' => $unit->dayPrice($date_in->format('l')),
                    'month' => $unit->monthPrice(),
                    'hour' => $unit->hourPrice(),
                ],
                'has_reservation' => $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_in,$currentDatesHolder),
                'reservations' => $unit->getReservations($date_in)
            ];
        }

        return $result;
    }
    public function checkPreviousReservationAndFutureReservations(Request $request)
    {

        $checkOldReservation =  Reservation::where('team_id', auth()->user()->current_team_id)->where('checked_in', '!=', null)->where('checked_out', '=', null)->where('unit_id', $request->get('unitId'))->first();
        $futureReservationsCount =  Reservation::where('team_id', auth()->user()->current_team_id)->where('date_in', '>', now())->where('unit_id', $request->get('unitId'))->count();
        return response()->json(['reservation_id' => $checkOldReservation['id'], 'futureReservationsCount' => $futureReservationsCount]);
    }
    public function getUnit(Request $request, $id)
    {
        $unit = Unit::with('onlineReservations')->find($id);
        return json_encode($unit);
    }
    public function servicesTaxInfo(Request $request)
    {
        $vat_percentage = \App\Handlers\Settings::get('tax') ? number_format(\App\Handlers\Settings::get('tax'), 2) : 0;
        $tourism_percentage = \App\Handlers\Settings::get('tourism_tax') ? number_format(\App\Handlers\Settings::get('tourism_tax'), 2) : 0;
        return response()->json(['vat_percentage' => $vat_percentage, 'tourism_percentage' =>  $tourism_percentage]);
    }
    public function notificationSteps()
    {
        // i need to check count of general settings keys count
        // check units count
        $generalSettings = [
            'day_start' => \App\Handlers\Settings::get('day_start'),
            'day_end' => \App\Handlers\Settings::get('day_end'),
            'accommodation_tax' => \App\Handlers\Settings::get('accommodation_tax'),
            'tax' => \App\Handlers\Settings::get('tax'),
            'tourism_tax' =>  \App\Handlers\Settings::get('tourism_tax')
        ];
        return response()->json(['settings_count' => count(array_keys($generalSettings)), 'units_count' => Unit::count()]);
    }
    public function unitReservationPopover(Request $request)
    {

        $reservation = Reservation::find($request->get('id'));
        $popoverData = new \stdClass();
        $popoverData->date_in = $reservation->date_in;
        $popoverData->date_out = $reservation->date_out;
        $popoverData->total_deposit = number_format($reservation->getDepositSum(), 2);
        $popoverData->total_withdraw = number_format($reservation->getWithdrawSum(), 2);
        $popoverData->leasing_price = number_format($reservation->total_price, 2);
        $popoverData->total_services = number_format($reservation->getServicesSum(), 2);
        $popoverData->total = number_format($reservation->getServicesSum() + $reservation->total_price, 2);
        $popoverData->balance = $reservation->balance / 100;
        $popoverData->checkin_date = $reservation->checked_in;


        return response()->json($popoverData);
    }


    /**
     * Get Special Prices and Apply them to override subtotal or swap prices if days in special price is null to get the original price
     * @param Request $request
     * @param $id
     * @param $start_date
     * @param $end_date
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecialPrices(Request $request, $id, $start_date, $end_date)
    {

        // Predefined  values
        $subtotalArr = [];

        $datesHasSpecialPrice = [];
        $datesDoesntHaveSpecialPrice = [];


        $unit = Unit::find($id);
        $unitCategory = $unit->unit_category;

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
            $incomingDatesPure [] =  $date->format('Y-m-d');
        }

        // Category Prices Per Day
        // $unitCategoryDaysPrices = $unitCategory->dailyByDayNamePrices();

        $unitCategoryDaysPrices = $unit->dailyByDayNamePrices();


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
                    $dates [] = $specialPriceObjectSkeleton;
                    $datesHasSpecialPrice [] = $specialPriceObjectSkeleton;
                }

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

            if($not_included){
                foreach($not_included as $obj){
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

            $range = $unitCategory->getDatesCustom(Carbon::parse($start_date), Carbon::parse($end_date));

            return response()->json([
                'status' => 'special_prices_found',
                'special_prices' => $specialPrices,
                'total_price' => $total_price,
                'subtotal' => $subtotal,
                'ewaTotal' => $ewaTotal,
                'vatTotal' => $vatTotal,
                'ttxTotal' => $ttxTotal,
                'datesHasSpecialPrice' => $datesHasSpecialPrice,
                'datesDoesntHaveSpecialPrice' => $datesDoesntHaveSpecialPrice,
                'incomingDates' => $incomingDatesPure,
                'unitCategoryDaysPrices' => $unitCategoryDaysPrices,
                'defaultInCaseOfSpecialPriceFound' => $range
            ]);
        } else {

            $range = $unitCategory->getDatesCustom(Carbon::parse($start_date), Carbon::parse($end_date));

            return response()->json(['status' => 'no_special_prices',   'incomingDates' => $incomingDatesPure , 'unitCategoryDaysPrices' => $range]);
        }
    }



    /**
     * @todo : must refactor the way days flush in the offers table ( to be fixed )
     */
    public function getOffers(Request $request, $id, $start_date, $end_date)
    {

        $unitCategory = Unit::find($id)->unit_category;

        $daysIncludedInOffers = [];

        $offers = Offer::whereNull('deleted_at')
            ->where('team_id', $unitCategory->team_id)
            ->where('enabled', 1)
            ->whereIntersectsStartDate($start_date)
            ->whereIntersectsEndDate($end_date)
            ->whereCategoryId($unitCategory->id)
            ->get();


        $periodFromIncomingDates = CarbonPeriod::dates(Carbon::parse($start_date), Carbon::parse($end_date)->subDay());
        $incomingDates = [];
        foreach ($periodFromIncomingDates as $date) {
            $incomingDates[] = $date->format('Y-m-d');
        }

        // Category Prices Per Day
        $unitCategoryDaysPrices = $unitCategory->dailyByDayNamePrices();

        if (count($offers)) {

            $offersDates = [];
            $periods = [];
            foreach ($offers as $offer) {

                // Ucfirst the array of days
                $days = array_map('ucfirst', $offer->days);



                $periodFromOfferDates = CarbonPeriod::dates(Carbon::parse($offer->start_date), Carbon::parse($offer->end_date)->subDay());
                $periods[$offer->id] = $periodFromOfferDates;

                $dates_chunked = $periods[$offer->id];
                foreach ($dates_chunked as $date) {
                    $offersDates[$offer->id][] = $date->format('Y-m-d');
                }




                $identifier = 1;
                foreach ($incomingDates as $date) {

                    if (in_array($date, $offersDates[$offer->id])) {
                        if (in_array(Carbon::parse($date)->format('l'), $days)) {



                            $objectSkeleton = new \stdClass();
                            $objectSkeleton->id = $identifier;
                            $objectSkeleton->date = $date;
                            $objectSkeleton->date_name = Carbon::parse($date)->format('l');
                            $objectSkeleton->discount_type = $offer->discount_type;
                            $objectSkeleton->discount_amount = $offer->discount_amount;
                            $daysIncludedInOffers[] = $objectSkeleton;
                        }
                    }

                    $identifier++;
                }
            }

            return response()->json([
                'status' => 'offers_found',
                'offers' => $offers,
                'daysIncludedInOffers' => $daysIncludedInOffers,
                'incomingDates' => $incomingDates,
                'unitCategoryDaysPrices' => $unitCategoryDaysPrices
            ]);
        }



        return response()->json(['status' => 'no_offers_found', 'incomingDates' => $incomingDates , 'unitCategoryDaysPrices' => $unitCategoryDaysPrices]);
    }


    public function checkConvertToUnderCleaningFromCheckout(Request $request){

        $reservation_id = $request->get('reservation_id');
        $unit_id        = $request->get('unit_id');

        $today = now()->format('Y-m-d');
        // if there were reservation after
        $future_reservation_count = Reservation::where('team_id', auth()->user()->current_team_id)
                                            ->where('date_in', '>=', $today)
                                            ->where('unit_id', $unit_id)
                                            ->whereNull('checked_out')
                                            ->where('status','!=', 'canceled')
                                            ->where('id' , '!=' , $reservation_id)
                                            ->get();

        return response()->json([ 'reservations' => $future_reservation_count , 'count' => count($future_reservation_count) , 'automatic_under_cleaning' => (bool) \App\Handlers\Settings::get('automatic_under_cleaning')]);
        // return $future_reservation_count;
    }

    public function renableDateOut(Request $request){

        $reservations = Reservation::where('team_id' , auth()->user()->current_team_id)->where('unit_id' , $request->get('unit_id'))->where('date_in' , '>' , $request->get('date'))->orderBy('date_in' , 'ASC')->first();
        return response()->json($reservations);
    }

    public function getOccupiedData(Request $request){


        $units = Unit::whereEnabled(true)
                        ->where('status', '!=', 0)
                        ->whereNull('deleted_at')
                        ->when($request->get('cat_id') != null , function ($query) use($request) {
                            $query->where('unit_category_id',$request->get('cat_id'));
                        })
                        ->get();

        $current_time = date('H:i:s');
        $current_date = $request->get('date');
        $date_with_time = date('Y-m-d H:i:s', strtotime("$current_date $current_time"));

        if(Carbon::parse($current_date)->isToday()){
            $occupied_units = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateTimeBetween($date_with_time)
            ->where('status' , 'confirmed')
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->with('unit')
            // ->where('date_out', '!=', $current_date)
            ->get();


            $reservedUnits = Reservation::whereIn('unit_id', $units->whereIn('status',[1,2,3])->pluck('id')->toArray())
            ->whereDateTimeBetween($date_with_time)
            ->where('status' , 'confirmed')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->with('unit')
            ->get();

        }else{
            $occupied_units = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateTimeBetween($date_with_time)
            ->where('status' , 'confirmed')
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->where('date_out', '!=', $current_date)
            ->with('unit')
            ->get();

            $reservedUnits = Reservation::whereIn('unit_id', $units->whereIn('status',[1,2,3])->pluck('id')->toArray())
            ->whereDateBetween(Carbon::parse($request->get('date'))->startOfDay())
            ->where('status' , 'confirmed')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->with('unit')
            ->get();
        }




        $paymentPreprocessor = Team::find(auth()->user()->current_team_id)->payment_preprocessor;


        $awaitingReservations = Reservation::where('team_id' , auth()->user()->current_team_id)
            ->where('date_in', '<=', Carbon::parse($request->get('date')))->where('date_out', '>', Carbon::parse($request->get('date')))
            ->where('status' , $paymentPreprocessor == 'fandaqah' ? 'awaiting-confirmation'  : 'awaiting-payment')
            ->whereNull('deleted_at')
            ->with('unit')
            ->orderBy('number' , 'desc')
            ->get();
        $under_cleaning_units_collection =  $units->where('status', 2);
        $under_maintenance_units_collection = $units->where('status', 3);
        $under_cleaning_units = $units->where('status', 2)->count();
        $under_maintenace_units = $units->where('status', 3)->count();

        $occupied_units_mapper = $occupied_units ? $occupied_units->map(function ($reservation) {
            return [
                    'id' => $reservation->id ,
                    'unit_id' => $reservation->unit_id,
                    'unit_number' => $reservation->unit->unit_number,
                    'status' => $reservation->status,
                    'date_in' => $reservation->date_in,
                    'date_out' => $reservation->date_out,
                    'checked_in' => $reservation->checked_in,
                    'flag' => 'occupied'
            ];
        }) : [];

        $reserved_units_mapper = $reservedUnits ? $reservedUnits->map(function ($reservation) {
            return [
                    'id' => $reservation->id ,
                    'unit_id' => $reservation->unit_id,
                    'unit_number' => $reservation->unit->unit_number,
                    'status' => $reservation->status,
                    'date_in' => $reservation->date_in,
                    'date_out' => $reservation->date_out,
                    'checked_in' => $reservation->checked_in,
                    'flag' => 'reserved'
            ];
        }) : [];

        // $awaiting_reservations_mapper = $awaitingReservations->map(function ($reservation) {
        //     return [
        //             'id' => $reservation->id ,
        //             'unit_id' => $reservation->unit_id,
        //             'unit_number' => $reservation->unit->unit_number,
        //             'status' => $reservation->status,
        //             'date_in' => $reservation->date_in,
        //             'date_out' => $reservation->date_out,
        //             'checked_in' => $reservation->checked_in,
        //             'flag' => 'awaiting'
        //     ];
        // });

        $units_ids = $units->pluck('id');
        $allMappers = new Collection(); //Create empty collection which we know has the merge() method
        $allMappers = $allMappers->merge($occupied_units_mapper);
        $allMappers = $allMappers->merge($reserved_units_mapper);
        // $allMappers = $allMappers->merge($awaiting_reservations_mapper);

        // logger($allMappers);
        // logger(json_decode($allMappers));
        $occupied_count = 0;
        $reserved_count = 0;
        $awaiting_count = 0;

        $categorization = [];
        foreach($allMappers as $mapper){
            if($mapper['flag'] == 'reserved'){
                $categorization[$mapper['unit_id']][]  = $mapper;
            }

            if($mapper['flag'] == 'occupied'){
                $categorization[$mapper['unit_id']][]  = $mapper;
            }
        }


        if($categorization){
            foreach($units_ids as $id){
                if(isset($categorization[$id])){
                    if(count($categorization[$id]) > 1){
                        foreach($categorization[$id] as $item){
                            if($item['flag'] == 'occupied'){
                                $occupied_count += 1;
                            }
                        }
                    }else{
                        foreach($categorization[$id] as $item){
                            if($item['flag'] == 'occupied'){
                                $occupied_count += 1;
                            }

                            if($item['flag'] == 'reserved'){
                                $reserved_count += 1;
                            }
                        }
                    }
                }
            }
        }






        // $available_units = count($units) - ($occupied_units + $reservedUnits + $awaitingReservations + $under_cleaning_units + $under_maintenace_units );
        $available_units = count($units) - ($occupied_count + $reserved_count + $under_cleaning_units + $under_maintenace_units );

        if(count($under_cleaning_units_collection)){
            foreach ($under_cleaning_units_collection as $under_cleaning_unit) {
                if(count($reserved_units_mapper)){
                    foreach ($reserved_units_mapper as $reservation) {
                        if($under_cleaning_unit->id == $reservation['unit_id']){
                            $available_units++;
                        }
                    }
                }
            }
        }

        if(count($under_maintenance_units_collection)){
            foreach ($under_maintenance_units_collection as $under_maintenance_unit) {
                if(count($reserved_units_mapper)){
                    foreach ($reserved_units_mapper as $reservation) {
                        if($under_maintenance_unit->id == $reservation['unit_id']){
                            $available_units++;
                        }
                    }
                }
            }
        }

        // $available_units = 0;
        if($request->get('unit_status')){

            switch ($request->get('unit_status')) {
                case 'checked_in':
                    if(!count($units))
                        $occupied_units = 0 ;

                    $reservedUnits = 0 ;
                    $awaitingPayment = 0 ;
                    $under_cleaning_units = 0 ;
                    $under_maintenace_units = 0 ;
                    $available_units = 0;
                break;
                case 'booked':
                    if(!count($units))
                        $reservedUnits = 0 ;

                    $occupied_units = 0 ;
                    $awaitingPayment = 0 ;
                    $under_cleaning_units = 0 ;
                    $under_maintenace_units = 0 ;
                    $available_units = 0;
                break;
                case 'awaiting-payment':
                    if(!count($units))
                        $awaitingPayment = 0 ;

                    $occupied_units = 0 ;
                    $reservedUnits = 0 ;
                    $under_cleaning_units = 0 ;
                    $under_maintenace_units = 0 ;
                    $available_units = 0;
                break;
                case 'available':
                    if(!count($units))
                        $available_units = 0 ;

                    $occupied_units = 0 ;
                    $reservedUnits = 0 ;
                    $awaitingPayment = 0 ;
                    $under_cleaning_units = 0 ;
                    $under_maintenace_units = 0 ;
                break;

                case 'under-cleaning':
                    if(!count($units))
                        $under_cleaning_units = 0 ;

                    $occupied_units = 0 ;
                    $reservedUnits = 0 ;
                    $awaitingPayment = 0 ;
                    $under_maintenace_units = 0 ;
                    $available_units = 0;
                break;
                case 'under-maintenance':
                    if(!count($units))
                        $under_maintenace_units = 0 ;

                    $occupied_units = 0 ;
                    $reservedUnits = 0 ;
                    $awaitingPayment = 0 ;
                    $under_cleaning_units = 0 ;
                    $available_units = 0;
                break;

            }

        }
// get the count of all enabled units
        $all_units = Unit::whereEnabled(true)
                        ->where('team_id' , auth()->user()->current_team_id)
                        ->whereNull('deleted_at')
                        ->count();


         $data = [
           'occupied_units' => $occupied_count,
           'reservedUnits' => $reserved_count,
           'available_units' => $available_units,
           'under_maintenace_units' => $under_maintenace_units,
           'under_cleaning_units' => $under_cleaning_units,
           'awaitingReservations' => count($awaitingReservations),
           'paymentPreprocessor' => $paymentPreprocessor,
              'unit_count' => $all_units

         ];
        return response()->json($data);

    }


    public function getReservationsDates(Request  $request){
        return response()->json(Unit::find($request->get('unit_id'))->getReservationsDates());
    }

    public function getAwaitingConfirmationReservationsCount(Request $request)
    {
        $awaitingCofirmationReservationsCount = DB::table('reservations as r')
        ->select('r.id as rid')
        ->where('r.team_id' , auth()->user()->current_team_id)
        ->where('r.status' ,  'awaiting-confirmation')
        ->whereNull('r.deleted_at')
        ->orderBy('r.number' , 'desc')
        ->count();
        return response()->json(['count' => $awaitingCofirmationReservationsCount]);
    }

    /**
     * Get All Reservations That Intersects
     *
     * @param Request $request
     * @return void
     */
    public function checkOverlapped(Request $request)
    {

        // its all about the request
        $request_start = Carbon::parse($request->start);
        $request_end = Carbon::parse($request->end);
        if ($request_start != $request_end) {
            $request_end->subDay();
        }
        $request_period = CarbonPeriod::create($request_start, $request_end);
        $request_dates = array();
        foreach ($request_period as $date) {
            $request_dates[] = $date->format('Y-m-d');
        }

        // its all about other reservations except current on the same unit
        $reservationsOnUnit = DB::table('reservations as r')
            ->select( 'r.id as id',
                'r.date_in as date_in',
                'r.date_out as date_out',
                'r.status'
            )
            ->whereNull('r.checked_out')
            ->whereNull('r.deleted_at')
            ->whereNotIn('r.status' , ['timeout','canceled'])
            ->where('r.unit_id' , $request->unit_id)
            ->where('r.id' , '!=' , $request->reservation_id)
            ->get();

        if($reservationsOnUnit){
            $dates = array();
            foreach ($reservationsOnUnit as $reservation) {
                $start = Carbon::parse($reservation->date_in);
                $end = Carbon::parse($reservation->date_out);
                if ($start != $end) {
                    $end->subDay();
                }
                $period = CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    $dates[] = $date->format('Y-m-d');
                }
            }
        }

        return response()->json(['has_reservation' => (bool) array_intersect($dates,$request_dates)]);

    }

    public function getAvailableUnitsForGroupReservation(Request $request)
    {
        // Getting posted params from the request
        $date_start = Carbon::parse($request->get('start'));
        $date_end = Carbon::parse($request->get('end'));
        $rent_type = $request->get('rent_type');
        $units_selected = $request->get('units_selected');

        // setup
        if ($date_start != $date_end) {
            $date_end->subDay();
        }

        $currentPeriodAccordingToSelectedDates = CarbonPeriod::create($date_start, $date_end);
        $currentDatesHolder = [];
        foreach ($currentPeriodAccordingToSelectedDates as $date) {
            if(!in_array($date->format('Y-m-d'),$currentDatesHolder)){
                $currentDatesHolder [] = $date->format('Y-m-d');
            }
        }

        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        // we will bypass the units that don't have month_price if the rent type selected was monthly
        if($rent_type == 'monthly'){
            $units = Unit::whereEnabled(true)
            ->where('team_id' , auth()->user()->current_team_id)
            ->whereNotNull('month_price')
            ->where('month_price' , '>' , 0)
            ->whereIn('status',[1,2])
            ->whereNull('deleted_at')
            ->get();
        }else{
            $units = Unit::whereEnabled(true)
            ->where('team_id' , auth()->user()->current_team_id)
            ->whereIn('status',[1,2])
            ->whereNull('deleted_at')
            ->get();
        }
        $results = [];
        $date_end = $date_end->addDay();
       
        foreach ($units as $unit) {
            $special_prices_array = [];
            // handle special price
                $special_prices =  $unit->unit_category && $unit->unit_category->special_prices ? $unit->unit_category->special_prices : [] ;
        
                if(count($special_prices)){
                    foreach ($special_prices as $special_price) {
                        $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                        foreach ($special_price_period as $special_price_date) {
                            // saftey in case special prices added with empty days prices 
                            if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                                $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                            } else {
                                $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                            }
                        }
                    }
                }

            $results['units'][] = [
                'id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'name' => $unit->name,
                'has_reservation' => $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder),
                'min_prices' => $unit->minPrices(),
                'prices' => $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($date_end), $rent_type,$special_prices_array),
            ];

        }

        $keys = array_column($results['units'], 'unit_number');
        if(!in_array(auth()->user()->current_team_id ,explode(',', env('ALPHABETICAL_TEAMS')))){
            array_multisort($keys, SORT_DESC, $results['units']);
        }
        $results['utility'] = [
            'SCTH' => Settings::checkIntegration('SCTH', auth()->user()->current_team_id),
            'SHMS' => Settings::checkIntegration('SHMS', auth()->user()->current_team_id),
            'customer_types' => Customer::customerTypes(),
            'purpose_of_visit' => Customer::purposeOfVisit()
        ];
        return response()->json($results);
    }

    /**
     * This function will determine if a unit has actually reservation or the unit is free
     *
     * @param [type] $unit
     * @param [type] $start_date
     * @param [type] $end_date
     * @return void
     */
    public function determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder)
    {
              /**
             * Here i will put my logic to handle the intersection based on the unit selected
             */

                $unitReservations = Reservation::where('unit_id' , $unit->id)
                                ->whereNUll('checked_out')
                                ->whereIn('status' , ['confirmed','awaiting-payment' , 'awaiting-confirmation'])
                                ->whereNull('deleted_at')
                                ->get();

                $unitDatesHolder = [];
                if(count($unitReservations)){
                    foreach($unitReservations as $unitReservation){
                        $start  = Carbon::parse($unitReservation->date_in);
                        $end  = Carbon::parse($unitReservation->date_out);
                        if ($start != $end) {
                            $end->subDay();
                        }
                        $period = CarbonPeriod::create($start, $end);
                        foreach ($period as $date) {
                            if(!in_array($date->format('Y-m-d'),$unitDatesHolder)){
                                $unitDatesHolder [] = $date->format('Y-m-d');
                            }
                        }
                    }
                }


                /**
                 * Checking the overlapping the right way -_-
                 */
                if(array_intersect($currentDatesHolder,$unitDatesHolder)){
                    $has_reservation = true;
                }else{
                    $has_reservation = false;
                }

                return $has_reservation;
    }

    public function createReservations(Request $request)
    {
        $groupedSpecialPricesArray = $request->get('groupedSpecialPricesArray');
        $company_id = $request->get('company_id');

        $company_check = Company::find($company_id);
        $customer_id = $request->get('customer_id');
        
        if(is_null($customer_id)){
            $customer_id = $company_check->customer_id;
        }

        $main_reservation = $request->get('main_reservation');
        $star_unit_id = $request->get('star_unit_id');
        $units_selected = $request->get('units_selected');
        // according to this condition this.star_unit_id && !this.main_reservation && this.units_selected.length > 1
        $filtered_units_selected = $request->get('filtered_units_selected');
        $source_id = $request->get('source_id');
        $source_number = $request->get('source_number');
        $date_start = Carbon::parse($request->get('date_start'));
        $date_end = Carbon::parse($request->get('date_end'));
        $rent_type = $request->get('rent_type') && $request->get('rent_type') == 'daily' ? 1 : 2;
        $team_id = auth()->user()->current_team_id;

        // dd($date_end->format('Y-m-d'));
        $currentPeriodAccordingToSelectedDates = CarbonPeriod::create($date_start,$date_end->subDay());
        
        $currentDatesHolder = [];
        foreach ($currentPeriodAccordingToSelectedDates as $date) {
            if(!in_array($date->format('Y-m-d'),$currentDatesHolder)){
                $currentDatesHolder [] = $date->format('Y-m-d');
            }
        }

        /**
         * the first scenario the user did not select a main reservation and by default a highlighted
         */
        if($star_unit_id){
            // i will create the main reservation on this star unit it , then proceeed attaching other reservations to it

            $counter = TeamCounter::where('team_id' , $team_id)->first();
            if(!$counter){
                $counter = TeamCounter::create();
                $counter->forceFill([
                    'team_id' => $team_id,
                ])->save();
            }
            $reservation_number = $counter->reservation_num;
            $counter->last_reservation_number =  $counter->reservation_num;
            $counter->save();
            // if count of selected units equal to 1
            if(count($units_selected) == 1){
                // this means user only selected one unit  m so we will create one reservation only
                $unit = Unit::find($units_selected[0]['id']);
                $special_prices_array = [];
                $special_prices =  $unit->unit_category && $unit->unit_category->special_prices ? $unit->unit_category->special_prices : [] ;
        
                if(count($special_prices)){
                    foreach ($special_prices as $special_price) {
                        $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                        foreach ($special_price_period as $special_price_date) {
                            // saftey in case special prices added with empty days prices 
                            if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                                $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                            } else {
                                $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                            }
                        }
                    }
                }
               

                if($unit->enabled != 1 && ($unit->status != 1 || $unit->status != 2)){
                    // if the unit is not enabled or not available ( in maintenance or cleaning)
                    return response()->json(['status' => false , 'message' => 'Unfortunately during the booking process , the unit became un-available to book']);
                }
                // check in the real time if the unit still available
                $unitHasReservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder);
                if(!$unitHasReservation){
                    $actual_incoming_date_out = $date_end->addDay();
                    // i had to do this step cause we are subtracting in unit model and i had to subtract it in the begining of the method
                    // $date_end = $date_end->addDay();
                    // we will proceed
                    // $calculations = $unit->getDatesFromRange($date_start, $date_end->addDay(),$rent_type);
                    $calculations = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($actual_incoming_date_out), $request->get('rent_type'),$special_prices_array);
                    // $special_prices = $this->getSpecialPrices($request,$unit->id, $date_start, $date_end);
                    // if($special_prices->original['status'] == "special_prices_found"){
                    //     $calculations['total_price_raw'] = $special_prices->original['total_price'];
                    //     $calculations['price'] = $special_prices->original['subtotal'];
                    //     $calculations['total_vat'] = $special_prices->original['vatTotal'];
                    //     $calculations['total_ewa'] = $special_prices->original['ewaTotal'];
                    //     $calculations['total_ttx'] = $special_prices->original['ttxTotal'];
                    // }
                    $reservation = new Reservation();
                    $reservation->team_id = $team_id;
                    $reservation->unit_id = $unit->id;
                    $reservation->number = $reservation_number;
                    $reservation->source_id = $source_id;
                    $reservation->source_num = $source_number;
                    $reservation->rent_type = $rent_type;
                    $reservation->date_in = $date_start->format('Y-m-d');
                    $reservation->date_out = $actual_incoming_date_out->format('Y-m-d');
                    // attaching creator id for the reservation
                    $reservation->created_by = auth()->user()->id;
                    $reservation->total_price = $calculations['total_price_raw'];
                    $reservation->sub_total = $calculations['price'];
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

                    if(count($groupedSpecialPricesArray) and isset($groupedSpecialPricesArray[$unit->id])){
                        $reservation->special_prices = json_encode($groupedSpecialPricesArray[$unit->id]);
                    }

                    $reservation->reservation_type = 'group';
                    $reservation->company_id = $company_id;
                    if($customer_id){
                        $reservation->customer_id = $customer_id;
                    }
                    $reservation->old_prices = [
                        'prices' => $unit->prices(),
                        'min_prices' => $unit->minPrices(),
                        'tourism_percentage'    =>  $unit->getTourismTax(),
                        'vat_parentage'    =>  $unit->getVat(),
                        'ewa_parentage'    =>  $unit->getEwa(),
                    ];

                    $reservation->prices = $calculations;

                    try {
                        if($reservation->save()){



                            if(count($request->get('reservation_services_selected'))){
                                foreach ($request->get('reservation_services_selected') as $reservation_service) {
                                    $reservationServiceMapper = new ReservationServiceMapper();
                                    $reservationServiceMapper->reservation_id = $reservation->id;
                                    $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                                    $reservationServiceMapper->save();
                                }
                            }

                            try {

                                
                                $meta = [
                                    'category' => 'create_reservation',
                                    'statement' => 'Reservation Total Price',
                                ];
                               
                                DB::transaction(function () use ($reservation,$meta) {
                    
                                    $negativeAmount = floatval(-1 * $reservation->total_price) * 100;
                                    $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
                    
                                    // create wallet
                                    $wallet = Wallet::create(
                                        [
                                            'holder_type' => Reservation::class,
                                            'holder_id' => $reservation->id,
                                            'name' => 'Default Wallet',
                                            'slug' => 'default',
                                            'balance' => $negativeAmount,
                                            'created_at' => $currentTimestamp,
                                            'updated_at' => $currentTimestamp,
                                        ]
                                    );
                    
                                    // create transaction
                                    DB::table('transactions')->insert(
                                        [
                                            'payable_type' => 'App\Reservation',
                                            'payable_id' => $reservation->id,
                                            'wallet_id' => $wallet->id,
                                            'type' => 'withdraw',
                                            'transaction_flag' => 'normal',
                                            'amount' => $negativeAmount,
                                            'confirmed' => 1,
                                            'is_public' => 0,
                                            'created_by' => auth()->user()->id,
                                            'updated_by' => auth()->user()->id,
                                            'meta' => json_encode($meta),
                                            'uuid' => Str::uuid(),
                                            'created_at' => $currentTimestamp,
                                            'updated_at' => $currentTimestamp,
                                        ]
                                    );
                                });



                                // $reservation->forceWithdrawFloat($reservation->total_price, [
                                //     'category' => 'reservation',
                                //     'statement' => 'Reservation Total Price',
                                // ], true, false);

                                // get the balance for this singular
                                $balance = $reservation->wallet->decimal_places == 3 ? $reservation->balance / 1000 : $reservation->balance / 100;
                                // map the balance in the the mapper table
                                GroupReservationBalanceMapper::updateOrCreate(
                                    ['reservation_id' => $reservation->id],
                                    ['balance' => floatval($balance)]
                                );



                                $team_id=auth()->user()->current_team_id;
                                $team=Team::find($team_id);
                                if($team->enable_aiosell){
                                $this->updateAiosellInventory($request);
                                }
                                return response()->json(['success' => true , 'reservation' => $reservation , 'singular' => true, 'customer_id' => $customer_id]);
                            } catch (\Throwable $th) {
                                return response()->json(['success' => false , 'message' => $th->getMessage() , 'stack-trace' => $th->getTrace()]);
                            }

                        }

                    } catch (\Throwable $th) {
                        return response()->json(['success' => false , 'message' => $th->getMessage()]);
                    }


                }else{
                    return response()->json(['success' => false , 'message' => 'Unfortunately during the booking process , the unit has been booked']);
                }

            }

            if(count($units_selected) > 1) {
                // this means user selected many units , so we will create main reservation , the attach other reservations to it

                // getting main unit the will hold the main reservation
                $unit = Unit::find($star_unit_id);
                $actual_incoming_date_out =  $date_end->addDay();
               
                $unit = Unit::find($units_selected[0]['id']);
                $special_prices_array = [];
                $special_prices =  $unit->unit_category && $unit->unit_category->special_prices ? $unit->unit_category->special_prices : [] ;
        
                if(count($special_prices)){
                    foreach ($special_prices as $special_price) {
                        $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                        foreach ($special_price_period as $special_price_date) {
                            // saftey in case special prices added with empty days prices 
                            if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                                $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                            } else {
                                $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                            }
                        }
                    }
                }

               
                if($unit->enabled != 1 && ($unit->status != 1 || $unit->status != 2)){
                    // if the unit is not enabled or not available ( in maintenance or cleaning)
                    return response()->json(['status' => false , 'message' => 'Unfortunately during the booking process , the unit became un-available to book' , 'unit' => $unit]);
                }
                // check in the real time if the unit still available
                $unitHasReservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder);
                if(!$unitHasReservation){
                    // i had to do this step cause we are subtracting in unit model and i had to subtract it in the begining of the method
                    // $date_end = $date_end->addDay();
                    // we will proceed
                    // $calculations = $unit->getDatesFromRange($date_start, $actual_incoming_date_out ,$rent_type);
                    $calculations = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($actual_incoming_date_out), $request->get('rent_type'),$special_prices_array);
                    // $special_prices = $this->getSpecialPrices($request,$unit->id, $date_start, $date_end);
                    // if($special_prices->original['status'] == "special_prices_found"){
                    //     $calculations['total_price_raw'] = $special_prices->original['total_price'];
                    //     $calculations['price'] = $special_prices->original['subtotal'];
                    //     $calculations['total_vat'] = $special_prices->original['vatTotal'];
                    //     $calculations['total_ewa'] = $special_prices->original['ewaTotal'];
                    //     $calculations['total_ttx'] = $special_prices->original['ttxTotal'];
                    // }
                    // $actual_incoming_date_out =  $date_end->addDay();

                    // $after = $actual_incoming_date_out->format('Y-m-d');
                    $mainReservationBlueprint = new Reservation();
                    $mainReservationBlueprint->team_id = $team_id;
                    $mainReservationBlueprint->unit_id = $unit->id;
                    $mainReservationBlueprint->number = $reservation_number;
                    $mainReservationBlueprint->source_id = $source_id;
                    $mainReservationBlueprint->source_num = $source_number;
                    $mainReservationBlueprint->rent_type = $rent_type;
                    $mainReservationBlueprint->date_in = $date_start->format('Y-m-d');
                    $mainReservationBlueprint->date_out = $actual_incoming_date_out->format('Y-m-d');
                    // attaching creator id for the reservation
                    $mainReservationBlueprint->created_by = auth()->user()->id;
                    $mainReservationBlueprint->total_price = $calculations['total_price_raw'];
                    $mainReservationBlueprint->sub_total = $calculations['price'];
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

                    if(count($groupedSpecialPricesArray) and isset($groupedSpecialPricesArray[$unit->id])){
                        $mainReservationBlueprint->special_prices = json_encode($groupedSpecialPricesArray[$unit->id]);
                    }

                    $mainReservationBlueprint->reservation_type = 'group';
                    $mainReservationBlueprint->company_id = $company_id;
                    if($customer_id){
                        $mainReservationBlueprint->customer_id = $customer_id;
                    }
                    $mainReservationBlueprint->old_prices = [
                        'prices' => $unit->prices(),
                        'min_prices' => $unit->minPrices(),
                        'tourism_percentage'    =>  $unit->getTourismTax(),
                        'vat_parentage'    =>  $unit->getVat(),
                        'ewa_parentage'    =>  $unit->getEwa(),
                    ];

                    $mainReservationBlueprint->prices = $calculations;

                    try {
                        if($mainReservationBlueprint->save()){


                            if(count($request->get('reservation_services_selected'))){
                                foreach ($request->get('reservation_services_selected') as $reservation_service) {
                                    $reservationServiceMapper = new ReservationServiceMapper();
                                    $reservationServiceMapper->reservation_id = $mainReservationBlueprint->id;
                                    $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                                    $reservationServiceMapper->save();
                                }
                            }
                            try {

                                $meta = [
                                    'category' => 'create_reservation',
                                    'statement' => 'Reservation Total Price',
                                ];
                               
                                DB::transaction(function () use ($mainReservationBlueprint,$meta) {
                    
                                    $negativeAmount = floatval(-1 * $mainReservationBlueprint->total_price) * 100;
                                    $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
                    
                                    // create wallet
                                    $wallet = Wallet::create(
                                        [
                                            'holder_type' => Reservation::class,
                                            'holder_id' => $mainReservationBlueprint->id,
                                            'name' => 'Default Wallet',
                                            'slug' => 'default',
                                            'balance' => $negativeAmount,
                                            'created_at' => $currentTimestamp,
                                            'updated_at' => $currentTimestamp,
                                        ]
                                    );
                    
                                    // create transaction
                                    DB::table('transactions')->insert(
                                        [
                                            'payable_type' => 'App\Reservation',
                                            'payable_id' => $mainReservationBlueprint->id,
                                            'wallet_id' => $wallet->id,
                                            'type' => 'withdraw',
                                            'transaction_flag' => 'normal',
                                            'amount' => $negativeAmount,
                                            'confirmed' => 1,
                                            'is_public' => 0,
                                            'created_by' => auth()->user()->id,
                                            'updated_by' => auth()->user()->id,
                                            'meta' => json_encode($meta),
                                            'uuid' => Str::uuid(),
                                            'created_at' => $currentTimestamp,
                                            'updated_at' => $currentTimestamp,
                                        ]
                                    );
                                });
                                // $mainReservationBlueprint->forceWithdrawFloat($mainReservationBlueprint->total_price, [
                                //     'category' => 'reservation',
                                //     'statement' => 'Reservation Total Price',
                                // ], true, false);

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

                                foreach($filtered_units_selected as $unit_obj){

                                     // getting the attachables
                                    $attachables = Reservation::where('attachable_id' , $mainReservationBlueprint->id)
                                    ->withTrashed()
                                    ->whereIn('status',['confirmed','canceled'])
                                    ->orderBy('id','asc')
                                    ->get();

                                    if(!count($attachables)){
                                        // it means it is the first attach
                                        $reservation_number =  'A' . $mainReservationBlueprint->number;
                                    }else{
                                        $last_key = 0;
                                        $limit = "AZZZ";
                                        $alphabetArr = [];
                                        for($x = "A", $limit++; $x != $limit; $x++) {
                                            $alphabetArr [] = $x;
                                        }

                                        $last_child_reservation = collect($attachables)->sortByDesc('id')->first();
                                        $last_child_reservation_alpha = explode($mainReservationBlueprint->number , $last_child_reservation->number)[0];
                                        $last_child_reservation_alpha_index = array_search($last_child_reservation_alpha , $alphabetArr);
                                        $next_child_resservation_alpha = $alphabetArr[$last_child_reservation_alpha_index+1];
                                        $reservation_number =  $next_child_resservation_alpha . $mainReservationBlueprint->number;
                                    }

                                    $unit = Unit::find($unit_obj['id']);

                                    $temp_special_prices_array = [];
                                    $special_prices =  $unit->unit_category && $unit->unit_category->special_prices ? $unit->unit_category->special_prices : [] ;
                            
                                    if(count($special_prices)){
                                        foreach ($special_prices as $special_price) {
                                            $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                                            foreach ($special_price_period as $special_price_date) {
                                                // saftey in case special prices added with empty days prices 
                                                if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                                                    $temp_special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                                                } else {
                                                    $temp_special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                                                }
                                            }
                                        }
                                    }
                    

                                    if($unit->enabled != 1 && ($unit->status != 1 || $unit->status != 2)){
                                        // if the unit is not enabled or not available ( in maintenance or cleaning)
                                        return response()->json(['status' => false , 'message' => 'Unfortunately during the booking process , the unit became un-available to book']);
                                    }
                                    // check in the real time if the unit still available
                                    $unitHasReservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder);
                                    if(!$unitHasReservation){
                                        // i had to do this step cause we are subtracting in unit model and i had to subtract it in the begining of the method
                                        // $date_end = $date_end->addDay();
                                        // we will proceed
                                        // $calculations = $unit->getDatesFromRange($date_start, $date_end,$rent_type);
                                        $calculations = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($actual_incoming_date_out), $request->get('rent_type'),$temp_special_prices_array);

                                        // $special_prices = $this->getSpecialPrices($request,$unit->id, $date_start, $date_end);
                                        // if($special_prices->original['status'] == "special_prices_found"){
                                        //     $calculations['total_price_raw'] = $special_prices->original['total_price'];
                                        //     $calculations['price'] = $special_prices->original['subtotal'];
                                        //     $calculations['total_vat'] = $special_prices->original['vatTotal'];
                                        //     $calculations['total_ewa'] = $special_prices->original['ewaTotal'];
                                        //     $calculations['total_ttx'] = $special_prices->original['ttxTotal'];
                                        // }
                                        $reservation = new Reservation();
                                        $reservation->team_id = $team_id;
                                        $reservation->unit_id = $unit->id;
                                        $reservation->number = $reservation_number;
                                        $reservation->source_id = $source_id;
                                        $reservation->source_num = $source_number;
                                        $reservation->rent_type = $rent_type;
                                        $reservation->date_in = $date_start->format('Y-m-d');
                                        $reservation->date_out = $actual_incoming_date_out->format('Y-m-d');
                                        // attaching creator id for the reservation
                                        $reservation->created_by = auth()->user()->id;
                                        $reservation->total_price = $calculations['total_price_raw'];
                                        $reservation->sub_total = $calculations['price'];
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

                                        if(count($groupedSpecialPricesArray) and isset($groupedSpecialPricesArray[$unit->id])){
                                            $reservation->special_prices = json_encode($groupedSpecialPricesArray[$unit->id]);
                                        }
                                        
                                        $reservation->reservation_type = 'group';
                                        $reservation->company_id = $company_id;
                                        if($customer_id){
                                            $reservation->customer_id = $customer_id;
                                        }

                                        $reservation->attachable_id = $mainReservationBlueprint->id;
                                        $reservation->old_prices = [
                                            'prices' => $unit->prices(),
                                            'min_prices' => $unit->minPrices(),
                                            'tourism_percentage'    =>  $unit->getTourismTax(),
                                            'vat_parentage'    =>  $unit->getVat(),
                                            'ewa_parentage'    =>  $unit->getEwa(),
                                        ];

                                        $reservation->prices = $calculations;

                                        try {
                                            if($reservation->save()){

                                                if(count($request->get('reservation_services_selected'))){
                                                    foreach ($request->get('reservation_services_selected') as $reservation_service) {
                                                        $reservationServiceMapper = new ReservationServiceMapper();
                                                        $reservationServiceMapper->reservation_id = $reservation->id;
                                                        $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                                                        $reservationServiceMapper->save();
                                                    }
                                                }
                                                try {

                                                    $meta = [
                                                        'category' => 'create_reservation',
                                                        'statement' => 'Reservation Total Price',
                                                    ];
                                                   
                                                    DB::transaction(function () use ($reservation,$meta) {
                                        
                                                        $negativeAmount = floatval(-1 * $reservation->total_price) * 100;
                                                        $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
                                        
                                                        // create wallet
                                                        $wallet = Wallet::create(
                                                            [
                                                                'holder_type' => Reservation::class,
                                                                'holder_id' => $reservation->id,
                                                                'name' => 'Default Wallet',
                                                                'slug' => 'default',
                                                                'balance' => $negativeAmount,
                                                                'created_at' => $currentTimestamp,
                                                                'updated_at' => $currentTimestamp,
                                                            ]
                                                        );
                                        
                                                        // create transaction
                                                        DB::table('transactions')->insert(
                                                            [
                                                                'payable_type' => 'App\Reservation',
                                                                'payable_id' => $reservation->id,
                                                                'wallet_id' => $wallet->id,
                                                                'type' => 'withdraw',
                                                                'transaction_flag' => 'normal',
                                                                'amount' => $negativeAmount,
                                                                'confirmed' => 1,
                                                                'is_public' => 0,
                                                                'created_by' => auth()->user()->id,
                                                                'updated_by' => auth()->user()->id,
                                                                'meta' => json_encode($meta),
                                                                'uuid' => Str::uuid(),
                                                                'created_at' => $currentTimestamp,
                                                                'updated_at' => $currentTimestamp,
                                                            ]
                                                        );
                                                    });

                                                    // $reservation->forceWithdrawFloat($reservation->total_price, [
                                                    //     'category' => 'reservation',
                                                    //     'statement' => 'Reservation Total Price',
                                                    // ], true, false);

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
                                            return response()->json(['success' => false , 'message' => $th->getMessage()]);
                                        }


                                    }else{
                                        // return response()->json(['success' => false , 'message' => 'Unfortunately during the booking process , the unit has been booked']);
                                    }

                                }
                                $team_id=auth()->user()->current_team_id;
                                $team=Team::find($team_id);
                                if($team->enable_aiosell){
                                $this->updateAiosellInventory($request);
                                }

                                return response()->json(['success' => true , 'reservation' => $mainReservationBlueprint , 'singular' => false , 'customer_id' => $customer_id]);
                            } catch (\Throwable $th) {
                                return response()->json(['success' => false , 'message' => $th->getMessage() , 'stack-trace' => $th->getTrace()]);
                            }

                        }

                    } catch (\Throwable $th) {
                        return response()->json(['success' => false , 'message' => $th->getMessage()]);
                    }


                }else{
                    return response()->json(['success' => false , 'message' => 'Unfortunately during the booking process , some units has been booked']);
                }

                return response()->json($filtered_units_selected);

            }

        }else{
            // this means that there is a main reservation selected
             /**
             * After creating the main reservation , we need to attach other reservations to it
             */
            $actual_incoming_date_out =  $date_end->addDay();
            foreach($units_selected as $unit_obj){

                // getting the attachables
                $attachables = Reservation::where('attachable_id' , $main_reservation['id'])
                ->withTrashed()
                ->whereIn('status',['confirmed','canceled'])
                ->orderBy('id','asc')
                ->get();

                if(!count($attachables)){
                    // it means it is the first attach
                    $reservation_number =  'A' . $main_reservation['reservation_number'];
                }else{
                    $last_key = 0;
                    $limit = "AZZZ";
                    $alphabetArr = [];
                    for($x = "A", $limit++; $x != $limit; $x++) {
                        $alphabetArr [] = $x;
                    }

                    $last_child_reservation = collect($attachables)->sortByDesc('id')->first();
                    $last_child_reservation_alpha = explode($main_reservation['reservation_number'] , $last_child_reservation->number)[0];
                    $last_child_reservation_alpha_index = array_search($last_child_reservation_alpha , $alphabetArr);
                    $next_child_resservation_alpha = $alphabetArr[$last_child_reservation_alpha_index+1];
                    $reservation_number =  $next_child_resservation_alpha . $main_reservation['reservation_number'];
                }

                $unit = Unit::find($unit_obj['id']);

                $temp_special_prices_array = [];
                $special_prices =  $unit->unit_category && $unit->unit_category->special_prices ? $unit->unit_category->special_prices : [] ;
        
                if(count($special_prices)){
                    foreach ($special_prices as $special_price) {
                        $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                        foreach ($special_price_period as $special_price_date) {
                            // saftey in case special prices added with empty days prices 
                            if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                                $temp_special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                            } else {
                                $temp_special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice($unit,Carbon::parse($special_price_date)->format('l')));
                            }
                        }
                    }
                }


                if($unit->enabled != 1 && ($unit->status != 1 || $unit->status != 2)){
                    // if the unit is not enabled or not available ( in maintenance or cleaning)
                    return response()->json(['status' => false , 'message' => 'Unfortunately during the booking process , the unit became un-available to book']);
                }
                // check in the real time if the unit still available
                $unitHasReservation = $this->determineIfUnitHasActualReservationAccordingToStartDate($unit,$date_start,$currentDatesHolder);
                if(!$unitHasReservation){
                    // i had to do this step cause we are subtracting in unit model and i had to subtract it in the begining of the method
                    // $date_end = $date_end->addDay();
                    // we will proceed
                    // $calculations = $unit->getDatesFromRange($date_start, $date_end ,$rent_type);
                    $calculations = $unit->getDatesFromRange(Carbon::parse($date_start), Carbon::parse($actual_incoming_date_out), $request->get('rent_type'),$temp_special_prices_array);

                    // $actual_incoming_date_out =  $date_end->addDay();
                    $reservation = new Reservation();
                    $reservation->team_id = $team_id;
                    $reservation->unit_id = $unit->id;
                    $reservation->number = $reservation_number;
                    $reservation->source_id = $source_id;
                    $reservation->source_num = $source_number;
                    $reservation->rent_type = $rent_type;
                    $reservation->date_in = $date_start->format('Y-m-d');
                    $reservation->date_out = $actual_incoming_date_out->format('Y-m-d');
                    // attaching creator id for the reservation
                    $reservation->created_by = auth()->user()->id;
                    $reservation->total_price = $calculations['total_price_raw'];
                    $reservation->sub_total = $calculations['price'];
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

                    if(count($groupedSpecialPricesArray) and isset($groupedSpecialPricesArray[$unit->id])){
                        $reservation->special_prices = json_encode($groupedSpecialPricesArray[$unit->id]);
                    }
                    
                    $reservation->reservation_type = 'group';
                    $reservation->company_id = $company_id;
                    if($customer_id){
                        $reservation->customer_id = $customer_id;
                    }
                    $reservation->attachable_id = $main_reservation['id'];
                    $reservation->old_prices = [
                        'prices' => $unit->prices(),
                        'min_prices' => $unit->minPrices(),
                        'tourism_percentage'    =>  $unit->getTourismTax(),
                        'vat_parentage'    =>  $unit->getVat(),
                        'ewa_parentage'    =>  $unit->getEwa(),
                    ];

                    $reservation->prices = $calculations;

                    try {
                        if($reservation->save()){


                            if(count($request->get('reservation_services_selected'))){
                                foreach ($request->get('reservation_services_selected') as $reservation_service) {
                                    $reservationServiceMapper = new ReservationServiceMapper();
                                    $reservationServiceMapper->reservation_id = $reservation->id;
                                    $reservationServiceMapper->reservation_service_id = $reservation_service['id'];
                                    $reservationServiceMapper->save();
                                }
                            }
                            try {

                                $meta = [
                                    'category' => 'create_reservation',
                                    'statement' => 'Reservation Total Price',
                                ];
                               
                                DB::transaction(function () use ($reservation,$meta) {
                    
                                    $negativeAmount = floatval(-1 * $reservation->total_price) * 100;
                                    $currentTimestamp = Carbon::now()->format('Y-m-d H:i:s');
                    
                                    // create wallet
                                    $wallet = Wallet::create(
                                        [
                                            'holder_type' => Reservation::class,
                                            'holder_id' => $reservation->id,
                                            'name' => 'Default Wallet',
                                            'slug' => 'default',
                                            'balance' => $negativeAmount,
                                            'created_at' => $currentTimestamp,
                                            'updated_at' => $currentTimestamp,
                                        ]
                                    );
                    
                                    // create transaction
                                    DB::table('transactions')->insert(
                                        [
                                            'payable_type' => 'App\Reservation',
                                            'payable_id' => $reservation->id,
                                            'wallet_id' => $wallet->id,
                                            'type' => 'withdraw',
                                            'transaction_flag' => 'normal',
                                            'amount' => $negativeAmount,
                                            'confirmed' => 1,
                                            'is_public' => 0,
                                            'created_by' => auth()->user()->id,
                                            'updated_by' => auth()->user()->id,
                                            'meta' => json_encode($meta),
                                            'uuid' => Str::uuid(),
                                            'created_at' => $currentTimestamp,
                                            'updated_at' => $currentTimestamp,
                                        ]
                                    );
                                });

                                // $reservation->forceWithdrawFloat($reservation->total_price, [
                                //     'category' => 'reservation',
                                //     'statement' => 'Reservation Total Price',
                                // ], true, false);

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
                        return response()->json(['success' => false , 'message' => $th->getMessage()]);
                    }


                }else{
                    // return response()->json(['success' => false , 'message' => 'Unfortunately during the booking process , the unit has been booked']);
                }

            }

            return response()->json(['success' => true , 'reservation' => $main_reservation , 'singular' => false, 'customer_id' => $customer_id]);

        }
        return response()->json($request->all());
    }




    public function updateAiosellInventory($request ){
        $units = $request['units_selected'];
        foreach($units as $unit){

            $unit_id = $unit['id'];
            $start_date = $request['date_start'];
            $end_date = $request['date_end'];
            //convert start date and end date to carbon
            $start_date = Carbon::parse($start_date);
            $end_date = Carbon::parse($end_date);
            $team_id = auth()->user()->current_team_id;
            $unit_category_id = Unit::find($unit_id)->unit_category_id;
            // get the unit category with id = $unit_category_id
            $unit_category = UnitCategory::find($unit_category_id);
            // dd($unit_category);
            $roomsData = [];
            for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
                $dayData = [
                    'from' => $date->format('Y-m-d'),
                    'to' => $date->copy()->addDay()->format('Y-m-d'), // End date is the next day
                    // You can add more fields here as needed
                ];
                $roomsData[] = $dayData;
            }
            $room = [];
            foreach ($roomsData as $date) {

                $from = $date['from'];
                $to = $date['to'];
                $dayName = Carbon::parse($from)->format('l');

                $all_units = Unit::where('unit_category_id', $unit_category_id)
                ->where('status', '!=', 3)
                ->where('deleted_at', null)->pluck('id')->toArray();
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
                            "available" => $available_units ,
                            "roomCode" => strval($unit_category_id)
                        ]
                    ]
                ]

        ;

                    // push the data to the room array
                    array_push($room, $data);




            }


            $data = [
                "hotelCode" => auth()->user()->current_team_id,
                "updates" => $room
            ];



            $URL=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/data';
            $username = env('AIOSELL_MEDIATOR_API_USERNAME');
            $password = env('AIOSELL_MEDIATOR_API_PASSWORD');



            $curl = curl_init();

            curl_setopt_array($curl, array(
               CURLOPT_URL => $URL,
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
        }



    }
    public function updateMytravelInventoryOnStatus($request){

            $unit_id = $request;
            // get now date
            $unit_category_id = Unit::find($unit_id)->unit_category_id;
            $unit_category = UnitCategory::find($unit_category_id);


            $start = Carbon::now()->format('Y-m-d');
            $end = Carbon::now()->addDay()->format('Y-m-d');

            $available_arr = [];
            $inventory_data = [];
            for ($i = 0; $i < 90; $i++) {
                $all_units = Unit::where('unit_category_id', $unit_category_id)
                    ->where('status', '!=', 3)
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
                    'category_id' => $unit_category_id,
                    'date_start' => $start,
                    'date_end' => $start,
                    'available_units' => $available_units,
                    'create_user' => 1,
                    'update_user' => 1
                ];

                $start = $end;
                $end = Carbon::parse($end)->addDay()->format('Y-m-d');

            }
            $data = [
                'category_id' => $unit_category_id,
                'inventory' => $inventory_data
            ];
            $curl = curl_init();
            $url = env('MYTRAVEL_API_URL') . '/api/hotel/room/status/update';
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
    }
    public function updateAiosellInventoryOnStatus($request){


        $unit_id = $request;
        // get now date
        $unit_category_id = Unit::find($unit_id)->unit_category_id;
        $unit_category = UnitCategory::find($unit_category_id);
        // another way to get now date

    $start_date =Carbon::now() ;
    $end_date = null;
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

            $from = $date['from'];
            $to = $date['to'];
            $dayName = Carbon::parse($from)->format('l');

            $all_units = Unit::where('unit_category_id', $unit_category_id)
            ->where('status', '!=', 3)
            ->where('deleted_at', null)->pluck('id')->toArray();
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
                        "available" => $available_units ,
                        "roomCode" => strval($unit_category_id)
                    ]
                ]
            ]

    ;

                // push the data to the room array
                array_push($room, $data);




        }


        $data = [
            "hotelCode" => auth()->user()->current_team_id,
            "updates" => $room
        ];

        $URL=env('AIOSELL_MEDIATOR_API_URL').'/api/pms/data';
        $username = env('AIOSELL_MEDIATOR_API_USERNAME');
        $password = env('AIOSELL_MEDIATOR_API_PASSWORD');


        $curl = curl_init();

        curl_setopt_array($curl, array(
           CURLOPT_URL => $URL,
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

    }

    public function showReservationWithoutCustomer(Request $request , $id)
    {
        $reservation = Reservation::with('customer', 'unit', 'creator', 'comments', 'customer.guests', 'reservation_guests' , 'source', 'invoices', 'promissory','depositInsuranceTransactions', 'withdrawInsuranceTransactions','company','company.reservations','reservationFreeServices','pure_invoices_without_credit_notes')->find($id);
        /** @var Unit $unit */
        $unit = Unit::whereId($reservation->unit_id)->withTrashed()->first();

        $reservation->wallet->refreshBalance();
        $date_start = Carbon::parse($reservation['date_in']);
        $date_end = Carbon::parse($reservation['date_out']);

        $reservation['Unifonic'] = Settings::checkIntegration('Unifonic', $reservation->team_id);
        $reservation['SCTH'] = Settings::checkIntegration('SCTH', $reservation->team_id);
        $reservation['SHMS'] = Settings::checkIntegration('SHMS', $reservation->team_id);
        //        $reservation['logs'] = $reservation->activities()->with('causer')->get();
        $reservation['logs'] = $reservation->logs();
        $reservation['transactions'] = $reservation->transactions;
        $reservation['services'] = $reservation->services;
        $reservation['balance'] = $reservation->balance;
        // initiate group_reservation fillings
        if($reservation->reservation_type == 'group'){
            $reservation['attachable_reservations_count'] = count($reservation->attachedReservations());
            $transactions = [];
            $services = [];
            $balances = [];
            $shared_invoices = [];
            $promissories = [];
            $main_reservation = null ;
            $push_main_reservation_to_collection = false;
            if(is_null($reservation->attachable_id)){
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            }else{
                $main_reservation = Reservation::find($reservation->attachable_id);
                 $push_main_reservation_to_collection = true;
            }


            if($main_reservation->status == 'canceled'){
                $reservations = Reservation::with('wallet','unit')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->where('status' , 'canceled')
                ->whereNull('deleted_at')
                ->get();

            }else{
                $reservations = Reservation::with('wallet','unit')
                ->where('reservation_type' , 'group')
                ->where('company_id' , $reservation->company_id)
                ->where(function ($query) use($reservation,$main_reservation) {
                    return $query->with('unit')->where('id',$reservation->id)->orWhere('attachable_id',$main_reservation->id);
                })
                ->whereIn('status' , ['confirmed','awaiting-payment'])
                // ->whereNull('checked_out')
                ->whereNull('deleted_at')
                ->get();

            }

            if($push_main_reservation_to_collection){
                $reservations->push($main_reservation);
            }
            $all_grouped_reservations_ids = [];
            foreach($reservations as $reservationObject){
                $balances [] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
                $all_grouped_reservations_ids [] = $reservationObject->id;
                if($reservationObject->promissory){
                    $promissories [] = $reservationObject->promissory;
                }

                if($reservationObject->invoices()->count()){
                    foreach($reservationObject->invoices as $invoice){
                        $shared_invoices [] = $invoice;
                    }
                }

                if($reservationObject->transactions()->count()){
                    foreach($reservationObject->transactions as $transaction){
                        $transactions [] = $transaction;
                    }
                }

                if($reservationObject->services()->count()){
                    foreach($reservationObject->services as $transaction){
                        $services [] = $transaction;
                    }
                }
            }
            foreach ($reservations as $obj) {
                GroupReservationBalanceMapper::updateOrCreate(
                    ['reservation_id' => $obj->id],
                    ['balance' => floatval(array_sum($balances) / count($reservations))]
                );
            }

            $reservation['group_reservation_transactions'] = collect($transactions)->sortByDesc('number')->values();
            $reservation['group_reservation_services'] = collect($services)->sortByDesc('service_log_number')->values();
            $reservation['group_balance'] = array_sum($balances);
            $reservation['shared_promissory'] = count($promissories) ? $promissories[0] : null;
            $reservation['shared_invoices'] = collect($shared_invoices)->sortByDesc('number')->values();
            $reservation['all_grouped_reservations'] = $reservations;
            $reservation['all_grouped_reservations_ids'] = $all_grouped_reservations_ids;
            $reservation['dates_calculations'] = startAndEndDateCalculatorWithNights($reservations);
            $reservation['main_reservation_id'] = $main_reservation->id;
        }

        $reservation['invoice_url'] = url("/home/reservation/pdf/invoice/{$reservation->id}");
        //        $reservation['has_checkin'] = Reservation::where('checked_in', '!=',null)->where('checked_out','=',null)->where('unit_id',$reservation->unit_id)->first();

        // became appended attribute inside model
        //        $reservation['hash_id'] = Hashids::connection('fandaqah')->encode($reservation->id);
        $reservation['url_current'] = \Config::get('app.url');
        $reservation['logout_icon'] = asset('img/logout.png');
        $reservation['login_icon'] = asset('img/enter.png');

        // became appended attribute inside model
        $reservation['day_start'] = \App\Handlers\Settings::get('day_start');
        $reservation['day_end'] = \App\Handlers\Settings::get('day_end');
        $reservation['messages_logs'] = $reservation->messagesLog()->get(['type', 'message', 'created_at'])->toArray();
        $reservation['customerNotes'] = $reservation->customer ? $reservation->customer->comments : null;
        $reservation['customer_id_number'] = $reservation->customer ? $reservation->customer->id_number : null;

        $reservation['prices'] = $reservation->prices ? $reservation->prices : $unit->getDatesFromRange($date_start, $date_end, $reservation->rent_type);
        $reservation['change_rate'] = $reservation->change_rate;
        $reservation['old_prices'] = $reservation->old_prices;

        return response()->json($reservation);
    }
}
