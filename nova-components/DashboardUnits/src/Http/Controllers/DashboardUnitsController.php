<?php

namespace SureLab\DashboardUnits\Http\Controllers;

use App\FormIntegration;
use App\Team;
use App\Unit;
use App\User;
use App\Occupied;
use Carbon\Carbon;
use App\Reservation;
use App\UnitCategory;
use App\Handlers\Settings;
use App\OnlineReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Surelab\Settings\ValueObjects\SettingRegister;
use App\Http\Resources\Charts\UnitCategoryResource;
use App\Http\Resources\Corneer\OnlineReservationResource;
use App\Http\Resources\Index\ReservationIndexPanelsResource;
use App\Http\Resources\UnitHousing\UnitHousingPanelsResource;

class DashboardUnitsController extends Controller
{

    //getMaintanceMsg
    public function getMaintanceMsg(Request $request)
    {
        $maintenance = env('MAINTENANCE', false);
        $date = now()->addDay()->format('d/m/Y');
        // dd($date);

        return response()->json([
            'maintenance' => $maintenance,
            'message' => __('maintenance_message', ['date' => $date]),
        ]);

    }

    public function getArrivals(Request $request)
    {
        $date = $request->get('date');
        $status = $request->get('status');
        $reservations = Reservation::with('unit','customer','company','wallet')
        ->where('team_id' , auth()->user()->current_team_id)
        ->where(function($query) use($date,$status){
            $query->where('date_in', '=', $date)
            ->where('team_id' , auth()->user()->current_team_id)
            ->whereIn('status' , ['confirmed' , 'awaiting-payment'])
            ->whereNull('deleted_at')
            ->when($status == 'pending' , function ($query) use($status) {
                $query->whereNull('checked_in');
            })
            ->when($status == 'checked_in' , function ($query) use($status) {
                $query->whereNotNull('checked_in');
            });
        })
        ->orWhere(function($query) use($date,$status){
            $query->where('checked_in', 'LIKE', "%{$date}%")
            ->where('team_id' , auth()->user()->current_team_id)
            ->whereIn('status' , ['confirmed' , 'awaiting-payment'])
            ->whereNull('deleted_at')
            ->when($status == 'pending' , function ($query) use($status) {
                $query->whereNull('checked_in');
            })
            ->when($status == 'checked_in' , function ($query) use($status) {
                $query->whereNotNull('checked_in');
            });
        })

        ->orderBy('id' , 'desc')
        ->paginate(4);
        return UnitHousingPanelsResource::collection($reservations);

    }

    public function getDepartures(Request $request)
    {
        $date = $request->get('date');
        $status = $request->get('status');
        $reservations = Reservation::with('unit','customer','company','wallet')
            ->where('team_id' , auth()->user()->current_team_id)
            ->where(function($query) use($date,$status){
                $query->where('date_out', '=', $date)
                    ->whereNull('checked_out')
                    ->whereNotNull('checked_in')
                    ->where('team_id' , auth()->user()->current_team_id)
                    ->where('status', '=', 'confirmed')
                    ->whereNull('deleted_at')
                    ->when($status == 'checked_in' , function ($query) use($status) {
                        $query->whereNotNull('checked_in')
                        ->whereNull('checked_out');
                    })
                    ->when($status == 'checked_out' , function ($query) use($status) {
                        $query->whereNotNull('checked_out');
                    });
            })
            ->orWhere(function($query) use($date,$status){
                $query->where('date_out', '=', $date)
                    ->whereNotNull('checked_out')
                    ->whereNotNull('checked_in')
                    ->where('checked_out' , '>' , $date)
                    ->where('team_id' , auth()->user()->current_team_id)
                    ->where('status', '=', 'confirmed')
                    ->whereNull('deleted_at')
                    ->when($status == 'checked_in' , function ($query) use($status) {
                        $query->whereNotNull('checked_in')
                        ->whereNull('checked_out');
                    })
                    ->when($status == 'checked_out' , function ($query) use($status) {
                        $query->whereNotNull('checked_out');
                    });
            })
            ->orWhere(function($query) use($date,$status){
                $query->where('checked_out', 'LIKE', "%{$date}%")
                    ->whereNotNull('checked_out')
                    ->whereNotNull('checked_in')
                    ->where('checked_out' , '>' , $date)
                    ->where('team_id' , auth()->user()->current_team_id)
                    ->where('status', '=', 'confirmed')
                    ->whereNull('deleted_at')
                    ->when($status == 'checked_in' , function ($query) use($status) {
                        $query->whereNotNull('checked_in')
                        ->whereNull('checked_out');
                    })
                    ->when($status == 'checked_out' , function ($query) use($status) {
                        $query->whereNotNull('checked_out');
                    });
            })
            ->orderBy('id' , 'desc')
            ->paginate(4);
            return UnitHousingPanelsResource::collection($reservations);

        // return response()->json(['depatrues' => $departures , 'checkout_time' => \App\Handlers\Settings::get('day_end')]);
    }

    public function getDeparturesOverdue(Request $request)
    {
        $day_end = Settings::get('day_end');
        $date = date('Y-m-d');
        $combinedDT = date('Y-m-d H:i:s', strtotime("$date $day_end"));
        $currentDate = Carbon::now('GMT+3')->format('Y-m-d') ;
        $overDueDepartures = DB::table('reservations as r')
            ->leftJoin('units as u','r.unit_id' , '=' ,'u.id')
            ->leftJoin('customer as c','r.customer_id' , '=' ,'c.id')
            ->leftJoin('wallets as w', function ($join) {
                $join->on('r.id', '=', 'w.holder_id')
                    ->where('w.holder_type' , 'App\Reservation');
            })
            ->select('r.id as rid',
                'r.number as rnum',
                'r.date_in as rdi',
                'r.date_out as rdo',
                'r.checked_in as rchi',
                'r.checked_out as rcho',
                'u.id as uid',
                'u.unit_number as unum',
                'c.id as cid',
                'c.name as cname',
                'c.phone as cphone',
                'w.balance as rb',
                'w.decimal_places as decimal_places')
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->where('date_out_time', '<=', $combinedDT)
            ->where('date_out' , '<' , $currentDate )
            ->where(['r.team_id' => auth()->user()->current_team_id])
            ->where('r.status', '=', Reservation::STATUS_CONFIRMED)
            ->whereNull('r.deleted_at')
            ->orderBy('r.number' , 'desc')
            ->paginate(4);

        return response()->json($overDueDepartures);

    }

    public function getAwaitingReservations(Request $request){

        $status = $request->get('payment_preprocessor') == 'fandaqah' ? 'awaiting-confirmation' : 'awaiting-payment';
        $reservations = DB::table('reservations as r')
            ->leftJoin('units as u','r.unit_id' , '=' ,'u.id')
            ->leftJoin('customer as c','r.customer_id' , '=' ,'c.id')
            ->leftJoin('wallets as w', function ($join) {
                $join->on('r.id', '=', 'w.holder_id')
                    ->where('w.holder_type' , 'App\Reservation');
            })
            ->select('r.id as rid',
                'r.number as rnum',
                'r.total_price as rtotalprice',
                'r.date_in as rdi',
                'r.date_out as rdo',
                'u.id as uid',
                'u.unit_number as unum',
                'c.id as cid',
                'c.name as cname',
                'c.email as cemail',
                'c.phone as cphone',
                'w.balance as rb',
                'w.decimal_places as decimal_places')
            ->where('r.team_id' , auth()->user()->current_team_id)
            ->where('r.status' , $status)
            ->whereNull('r.deleted_at')
            ->orderBy('r.number' , 'desc')
            ->paginate(10);

        return response()->json($reservations);
    }

    public function getCleaningData(Request $request){
        $today = Carbon::today()->startOfDay()->format('Y-m-d');

        $units = Unit::whereEnabled(true)->where('status', '!=', 0)->get();

        $occupied_units = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateBetween($today)
            ->whereNotIn('status' , ['canceled','timeout'])
            ->whereNull('checked_out')
            ->whereNotNull('checked_in')
            ->count();

        $reservedUnits = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateBetween(Carbon::now()->startOfDay())
            ->whereNotIn('status' , ['canceled','timeout'])
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();

        $under_cleaning_units = $units->where('status', 2)->count();
        $under_maintenace_units = $units->where('status', 3)->count();
        $available_units = count($units) - ($occupied_units + $reservedUnits + $under_cleaning_units + $under_maintenace_units );

        $cleaned = $available_units + $occupied_units + $reservedUnits ;

        $data = [
            'labels' => [__("Cleaned"), __("Under Cleaning") , __("Under Maintenance")],
            'datasets' => [
                [
                    'labels' => [ __("Cleaned"), __("Under Cleaning") , __("Under Maintenance")],
                    'backgroundColor' => ["#95d8b1", "#ff9019" , "#b3c0c7"],
                    'data' => [
                        $cleaned,
                        $under_cleaning_units,
                        $under_maintenace_units,
                    ],
                ]
            ],
        ];

        return response()->json($data);
    }

    public function getUnitCategoryOccupancyData(Request $request){

        $data = [
            'team_id' => $request->team_id,
            'date' => Carbon::today()->format('Y-m-d'),
            'lang' => $request->locale
        ];

        $headers =  [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. env('FANDAQAH_API_V2_AUTHORIZATION_BEARER')
        ];

        $url = env('FANDAQAH_API_V2_URL') . '/charts/occupany-per-category';
        $callGetUnitCategoriesOccupancyPerTeam = guzzleRequester('POST',$url,$headers,$data);
        return response()->json($callGetUnitCategoriesOccupancyPerTeam);

    }

    /**
     * Count users to control show or hide or open receipt modal
     *
     * @param Request $request
     * @return void
     */
    public function countUsers(Request $request)
    {
        return response()->json(count(User::whereNull('deleted_at')->where('current_team_id' , auth()->user()->current_team_id)->get()));
    }


    /**
     * Get enabled unit categories
     *
     * @return void
     */
    public function getUnitCategories()
    {
        $categories =  UnitCategory::where('team_id' , auth()->user()->current_team_id)
                        ->whereHas('units')
                        ->whereNull('deleted_at')
                        ->orderByDesc('created_at')
                        ->get();
        return response()->json($categories);

    }

    public function getDayStartAndDayEndSettings(Request $request)
    {
        return response()->json(['day_start' => \App\Handlers\Settings::get('day_start') , 'day_end' => \App\Handlers\Settings::get('day_end')]);
    }

    public function getPaymenrPreprocessor(Request $request)
    {
        return response()->json(['payment_preprocessor' => Team::find(auth()->user()->current_team_id)->payment_preprocessor]);
    }

    public function createProspect(Request $request)
    {

        // Validate some backend
        $this->validate($request , [
            'prospectInfo.email' => 'email',
            'prospectInfo.phone' => ['regex:/(05)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/']
        ]);

        $prospectObj = FormIntegration::updateOrCreate(
            ['team_id' => $request->get('current_team_id') , 'integration_name' => 'alraedah-finance'],
            [
            'team_id' => $request->get('current_team_id'),
            'integration_name' => 'alraedah-finance',
            'status' => 'pending',
            'data' => $request->get('prospectInfo')
        ]);
        if($prospectObj){
            return response()->json(['success' => true , 'prospectObj' => $prospectObj]);
        }else{
            return response()->json(['success' => false]);
        }

    }

    public function getOldProspect(Request $request)
    {
        return response()->json(FormIntegration::where('team_id',$request->current_team_id)->first());
    }

}
