<?php

namespace App\Http\Controllers;

use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use App\Handlers\Settings;
use Illuminate\Http\Request;
use Aghanem\Jawaly\Facades\Jawaly;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UnitHousing\UnitHousingPanelsResource;

class PanelsPrintController extends Controller
{


    public function preparePanelsPrint(Request $request , $type , $date)
    {
        $date_formatted = Carbon::parse($date)->format('Y/m/d');
        $data = '';
        $title = '';
        $status = $request->get('status');
        switch ($type) {
            case 'arrival':
                $title = __('Arrivals For Day') . ' ' . $date_formatted;
                $data = Reservation::with('unit','customer','company','wallet','groupReservationBalanceMapper')
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
                ->whereIn('status' , ['confirmed' , 'awaiting-payment'])
                ->whereNull('deleted_at')
                ->orderBy('id' , 'desc')
                ->get();
                break;

            case 'departure' :
                $title = __('Departures For Day') . ' ' . $date_formatted;
                $data = Reservation::with('unit','customer','company','wallet','groupReservationBalanceMapper')
                ->where('team_id' , auth()->user()->current_team_id)
                ->where(function($query) use($date,$status){
                    $query->where('date_out', '=', $date)
                        ->whereNull('checked_out')
                        ->whereNotNull('checked_in')
                        ->where('team_id' , auth()->user()->current_team_id)
                        ->where('status', '=', 'confirmed')
                        ->whereNull('deleted_at')
                        ->when($status == 'checked_in' , function ($query) use($status) {
                            $query->whereNotNull('checked_in');
                        })
                        ->when($status == 'checked_out' , function ($query) use($status) {
                            $query->whereNotNull('checked_out');
                        });
                })
                ->orWhere(function($query) use($date,$status){
                    $query->where('date_out', '=', $date)
                        ->whereNotNull('checked_out')
                        ->whereNotNull('checked_in')
                        ->where('team_id' , auth()->user()->current_team_id)
                        ->where('status', '=', 'confirmed')
                        ->whereNull('deleted_at')
                        ->when($status == 'checked_in' , function ($query) use($status) {
                            $query->whereNotNull('checked_in');
                        })
                        ->when($status == 'checked_out' , function ($query) use($status) {
                            $query->whereNotNull('checked_out');
                        });
                })
                ->orWhere(function($query) use($date,$status){
                    $query->where('checked_out', 'LIKE', "%{$date}%")
                        ->whereNotNull('checked_out')
                        ->whereNotNull('checked_in')
                        ->where('team_id' , auth()->user()->current_team_id)
                        ->where('status', '=', 'confirmed')
                        ->whereNull('deleted_at')
                        ->when($status == 'checked_in' , function ($query) use($status) {
                            $query->whereNotNull('checked_in');
                        })
                        ->when($status == 'checked_out' , function ($query) use($status) {
                            $query->whereNotNull('checked_out');
                        });
                })
                ->orderBy('id' , 'desc')
                ->get();

                break;

            case 'departure-overdue':
                $title = __('Over Out Dates Customers');
                $day_end = Settings::get('day_end');
                $date = date('Y-m-d');
                $combinedDT = date('Y-m-d H:i:s', strtotime("$date $day_end"));
                $currentDate = Carbon::now('GMT+3')->format('Y-m-d') ;
                $data = DB::table('reservations as r')
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
                        'r.total_price as total_price',
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
                    ->get()->toArray();

                break;


        }

        if(count($data)){
            $total_creditor = [];
            $total_debtor = [];
            $total_reservations = [];
            foreach ($data as $reservation) {

                if($reservation->reservation_type == 'single'){
                    $balance = $reservation->balance;
                    if ($balance > 0) {
                        // So it's Creditor
                        $total_creditor[]  = $balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
                    } else {
                        // So it's Debtor
                        $total_debtor[]  = $balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
                    }

                }else{
                    $balance = $reservation->groupReservationBalanceMapper->balance;
                    if ($balance > 0) {
                        $total_creditor[]  = $balance ;
                    } else {
                        $total_debtor[]  = $balance;
                    }
                }
                $total_reservations [] = $reservation->total_price + $reservation->getServicesSum();
            }

        }
if(!isset($total_creditor)) $total_creditor = [];
if(!isset($total_debtor)) $total_debtor = [];
if(!isset($total_reservations)) $total_reservations = [];
        $calculations = [
            'total_creditor' => number_format(array_sum($total_creditor),2),
            'total_debtor' => number_format(abs(array_sum($total_debtor)),2),
            'total_reservations' => number_format(array_sum($total_reservations),2),
        ];
        $data = UnitHousingPanelsResource::collection($data)->toArray($data);
        return view('print.panels-data' , ['data' => $data , 'count' => count($data) , 'title' => $title , 'locale' => app()->getLocale() , 'calculations' => $calculations , 'type' => $type]);
    }
}
