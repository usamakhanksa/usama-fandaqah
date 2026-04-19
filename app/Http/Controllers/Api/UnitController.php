<?php

namespace App\Http\Controllers\Api;

use App\Country;
use App\Customer;
use App\Events\UnitUpdated;
use App\Handlers\Settings;
use App\Http\Controllers\Controller;
use App\Http\Resources\CleaningResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\MaintenanceResource;
use App\Http\Resources\UnitResource;
use App\Occupied;
use App\Reservation;
use App\Unit;
use App\UnitCleaning;
use App\UnitMaintenance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Validator;
use Illuminate\Support\Facades\Storage;

class UnitController extends Controller
{
	/**
	* check Unit
    *
	* @return \Illuminate\Http\Response
	*/
	public function check(Request $request, Unit $unit)
	{
        $date_start = Carbon::parse($request->get('date_start'));
        $date_end = Carbon::parse($request->get('date_end'));
        $diff_days = $date_start->diffInDays($date_end);
        $diff_nights = ($diff_days === 0) ? 1 : $diff_days;

        $result = [
            'id' => $unit->id,
            'unit_number' => $unit->unit_number,
            'name' => $unit->name,
            // 'SCTH' => Settings::checkIntegration('SCTH', $unit->team_id),
            'purpose' => null,
            'has_reservation' => count($unit->getReservations($date_start)),
            'prices' => [
                'month' => $unit->monthPrice(),
            ],
            'reservation' => [
                'start_date' => $date_start->format('d-m-Y'),
                'end_date' => $date_end->format('d-m-Y'),
                'days' => $diff_days,
                'nights' => $diff_nights,
                'month' => ['count' => floor($diff_nights/30), 'nights' => $diff_nights%30],
                'prices' => $unit->getDatesFromRange($date_start, $date_end, $request->get('rent_type')),
            ],
            'reservations_date' => $unit->getReservationsDates(),
            'online_reservations' => $unit->getOnlineReservations($date_start)
        ];
        return response()->json(['data' => $result]);
    }
    /**
    *login api
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
		$date = Carbon::parse($request->date);
        $now = Carbon::now()->startOfDay();

        $units = Unit::whereEnabled(true)->where('status', '!=', 0)->get();

        if($date->gte($now)){
            $occupied_units = Reservation::whereIn('unit_id', $units->pluck('id')->toArray())
                ->whereDateBetween($date)
                ->where('status', '!=', 'canceled')
                ->whereNull('checked_out')
                ->count();

            $occupied_units_without_checked_out = Reservation::whereIn('unit_id', $units->pluck('id')->toArray())
                ->whereDateBetween($date)
                ->where(['status'=>'confirmed'])
                ->count();

            $occupied_percentage = ($units->count()) ? round($occupied_units_without_checked_out / $units->count() *100, 2): 0;
            $available_units = Unit::available($date)->count();
        }else{
            $available_units = 0;
            $occupied_percentage  = 0;
            $occupied_units = 0;
            $occ = Occupied::whereDate('created_at', $date)->first();
            if($occ){
                $occupied_units = $occ->occupied ?? false;
                $occupied_percentage = round($occ->percentage, 2) ?? false;
                $available_units = $occ->units_count ?? false;
            }
        }

		return  UnitResource::collection(
            Unit::whereEnabled(true)->with('reservations')->paginate()
        )->additional([
			'meta' => [
                'date' => $date->format('d-m-Y'),
            	'day_name' => $date->format('l'),
            	'available_units' => $available_units,
            	'occupied_percentage' => $occupied_percentage,
            ]
        ]);
	}

    /**
    * units under maintenance
    * @return \Illuminate\Http\Response
    */
    public function maintenance(Request $request)
    {
        $data = QueryBuilder::for(UnitMaintenance::class)
            ->with(['unit', 'creator'])
            ->allowedFilters([
                AllowedFilter::scope('status'),
            ])
            ->defaultSort('-start_at')
            ->whereIn('team_id', auth()->user()->teams->pluck('id'))
            ->allowedSorts('completed_at', 'start_at', 'id')
            ->paginate($request->get('per_page', 30));
        return  MaintenanceResource::collection($data);
    }


    /**
    * units under cleaning
    * @return \Illuminate\Http\Response
    */
    public function cleaning(Request $request)
    {
        $data = QueryBuilder::for(UnitCleaning::class)
            ->with(['unit', 'creator'])
            ->allowedFilters([
                AllowedFilter::scope('status'),
            ])
            ->defaultSort('-start_at')
            ->whereIn('team_id', auth()->user()->teams->pluck('id'))
            ->allowedSorts('completed_at', 'start_at', 'id')
            ->paginate($request->get('per_page', 30));

        return  CleaningResource::collection($data);
    }

    /**
    * complete unit Cleaning
    * @return \Illuminate\Http\Response
    */
    public function completeCleaning(Request $request, $id)
    {

        $images = array();

        $cleaning = UnitCleaning::withoutGlobalScopes()->find($id);
        $cleaning->completed_at = Carbon::now();
        $cleaning->team_id = auth()->user()->teams->first()->id;
        $cleaning->note = strip_tags(trim($request->note, '"'));
        $cleaning->completed_by = auth()->user()->id;


        if($request['images']){
            foreach ($request['images'] as $image){
                $name=time().$image->getClientOriginalName();
                $filePath = 'FandaqahHK/' . $name;
                Storage::disk('s3')->put($filePath, file_get_contents($image));
                $s3ImageUrl = Storage::disk('s3')->url($filePath);
                $images [] = $s3ImageUrl;
            }

            $cleaning->images = $images ;
        }

        $cleaning->save();

        $unit = Unit::withoutGlobalScopes()->find($cleaning->unit_id);
        $unit->status = Unit::STATUS_ENABLED;
        $unit->save();

        event(new UnitUpdated($unit));

        return new CleaningResource($cleaning);
    }

    /**
    * complete unit Maintenance
    * @return \Illuminate\Http\Response
    */
    public function completeMaintenance(Request $request, $id)
    {
        $maintenance = UnitMaintenance::find($id);
        $maintenance->completed_at = Carbon::now();
        $maintenance->note = $request->note;
        $maintenance->completed_by = auth()->user()->id;
        $maintenance->save();

        $maintenance->unit->status = 1;
        $maintenance->unit->save();

        return new MaintenanceResource($maintenance);
    }

    /**
     * Show the application splash screen.
     *
     * @return Response
     */
    public function charts()
    {
        $units = Unit::whereEnabled(true)->where('status', '!=', 0)->get();

        $occupied_units = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
                ->whereDateBetween(Carbon::now()->startOfDay())
                ->where('status', '!=', 'canceled')
                ->whereNull('checked_out')
                ->count();

        $under_cleaning_units = $units->where('status', 2)->count();
        $under_maintenace_units = $units->where('status', 3)->count();
        $available_units = Unit::available(Carbon::now()->startOfDay())->count();

        $today = [
            'total' => $units->count(),
            'labels' => [__("محجوز"), __("فارغ"), __("صيانة"), __("نظافة")],
            'datasets' => [
                [
                    'labels' => [__("محجوز"), __("فارغ"), __("صيانة"), __("نظافة")],
                    'backgroundColor' => ["#f6574b", "#50c669", "#b3c0c7", "#ff9019"],
                    'data' => [
                        $occupied_units,
                        $available_units,
                        $under_maintenace_units,
                        $under_cleaning_units
                    ],
                ]
            ],
        ];

        $items = Occupied::orderBy('created_at', 'desc')->take(7)->get();

        $week = [
            'labels' => [],
            'datasets' => [
                [
                    'label' =>  __("فارغ"),
                    'background_color' => "#50c669",
                    'data' => []
                ],
                [
                    'label' =>  __("محجوز"),
                    'background_color' => "#f6574b",
                    'data' => []
                ],
                [
                    'label' =>  __("نظافة"),
                    'background_color' => "#ff9019",
                    'data' => []
                ],
                [
                    'label' =>  __("صيانة"),
                    'background_color' => "#b3c0c7",
                    'data' => []
                ],
            ]
        ];
        foreach ($items as $item) {
            $week['labels'][] = __($item->created_at->format('l'));
            $week['datasets'][0]['data'][] = $item->available;
            $week['datasets'][1]['data'][] = $item->occupied;
            $week['datasets'][2]['data'][] = $item->cleaning;
            $week['datasets'][3]['data'][] = $item->maintenance;
        }

        return response([
            'today' => $today,
            'week' => $week,
        ]);
    }

    /**
     * @param Request $request
     * @param Unit $unit
     * @return UnitResource
     * @throws \Exception
     */
    public function changeStatus(Request $request, Unit $unit)
    {
        switch ($request->get('type')) {
            case 'cleaning':
                if($unit->status != Unit::STATUS_UNDER_CLEANING){
                    $unit->status = Unit::STATUS_UNDER_CLEANING;
                    UnitCleaning::create(['unit_id' =>  $unit->id, 'start_at'=>    new \DateTime()]);
                }
                break;
            case 'maintenance':
                if($unit->status != Unit::STATUS_UNDER_MAINTENANCE){
                    $unit->status = Unit::STATUS_UNDER_MAINTENANCE;
                    UnitMaintenance::create(['unit_id' =>  $unit->id, 'start_at'=>    new \DateTime()]);
                }
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

        event(new UnitUpdated($unit));

        return new UnitResource($unit);
    }
}
