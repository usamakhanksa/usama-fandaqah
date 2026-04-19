<?php

namespace App\Http\Controllers;

use App\Team;
use App\Unit;
use App\Occupied;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Http\Request;

class ChartsController extends Controller
{
    /**
     * Show the application splash screen.
     *
     * @return Response
     */
    public function occupied()
    {
    	$units = Unit::whereEnabled(true)->where('status', '!=', 0)->get();

    	$occupied_units = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
                ->whereDateBetween(Carbon::now()->startOfDay())
                ->whereNotIn('status' , ['canceled','timeout'])
                ->whereNull('checked_out')
                ->whereNotNull('checked_in')
                ->count();

    	$reservedUnits = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateBetween(Carbon::now()->startOfDay())
            ->where('status' , 'confirmed')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();

        $paymentPreprocessor = Team::find(auth()->user()->current_team_id)->payment_preprocessor;

        $awaitingReservations = Reservation::whereIn('unit_id', $units->where('status',1)->pluck('id')->toArray())
            ->whereDateBetween(Carbon::now()->startOfDay())
            ->where('status' , $paymentPreprocessor == 'fandaqah' ? 'awaiting-confirmation'  : 'awaiting-payment')
            ->whereNull('checked_out')
            ->whereNull('checked_in')
            ->count();

        $under_cleaning_units = $units->where('status', 2)->count();
        $under_maintenace_units = $units->where('status', 3)->count();
//        $available_units = Unit::available(Carbon::now()->startOfDay())->count();
        $available_units = count($units) - ($occupied_units + $reservedUnits + $awaitingReservations + $under_cleaning_units + $under_maintenace_units );


    	$today = [
            'total' => $units->count(),
    		'labels' => [__("سجل دخول"), __("محجوز") , $paymentPreprocessor == 'fandaqah' ? __("Awaiting confirmation") :  __("بانتظار الدفع") , __("متاح"), __("صيانة"), __("نظافة")],
    		'datasets' => [
    			[
	    			'labels' => [ __("سجل دخول") ,  __("محجوز"), $paymentPreprocessor == 'fandaqah' ? __("Awaiting confirmation") :  __("بانتظار الدفع") , __("متاح"), __("صيانة"), __("نظافة")],
	    			'backgroundColor' => ["#f6574b", "#126ee8" , "#9B59B6" , "#50c669", "#b3c0c7", "#ff9019"],
	    			'data' => [
	    				$occupied_units,
                        $reservedUnits,
                        $awaitingReservations,
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
                    'label' =>  __("متاح"),
                    'backgroundColor' => "#50c669",
		            'data' => []
	          	],

                [
                    'label' =>  __("سجل دخول"),
                    'backgroundColor' => "#f6574b",
                    'data' => []
                ],

	          	[
                    'label' =>  __("محجوز"),
                    'backgroundColor' => "#126ee8",
		            'data' => []
	          	],
                [
                    'label' =>  __("نظافة"),
                    'backgroundColor' => "#ff9019",
                    'data' => []
                ],
                [
                    'label' =>  __("صيانة"),
                    'backgroundColor' => "#b3c0c7",
                    'data' => []
                ],
	        ]
       	];

        foreach ($items as $item) {
            $week['labels'][] = __($item->created_at->format('l'))."\n". $item->created_at->format('d-m-Y');
            $week['datasets'][0]['data'][] = $item->available;
            $week['datasets'][1]['data'][] = $item->occupied;
            $week['datasets'][2]['data'][] = $item->booked;
            $week['datasets'][3]['data'][] = $item->cleaning;
            $week['datasets'][4]['data'][] = $item->maintenance;
        }

    	return response([
    		'today' => $today,
    		'week' => $week,
    	]);
    }
}
