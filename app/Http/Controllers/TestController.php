<?php

namespace App\Http\Controllers;

use App\Customer;
use Carbon\Carbon;
use App\Reservation;
use GuzzleHttp\Client;
use App\Handlers\Settings;
use Illuminate\Http\Request;
use App\Jobs\DailyBriefReportJob;
use Aghanem\Jawaly\Facades\Jawaly;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test(Request $request)
    {

        // simulate Staah fetching reservations 

        $base_url = 'https://fandaqahsandbox.staah.net';
        $headers = [
            'Authorization' => 'cExRcHBUZ2U6UW82SHN3YkY',
            'Content-Type' => 'application/json'
        ];

        $client = new Client([
            'allow_redirects' => true,
            'timeout' => 2000,
            'http_errors' => true
        ]);

        $body = json_encode(['hotel_id' => auth()->user()->current_team_id]);
        // return $body;
        $request = new Psr7Request('POST', $base_url . '/SUAPI/jservice/Reservation', $headers, $body);
        $res = $client->send($request);
        return response()->json($res->getBody()->getContents());
        // return view('email.owner.registration_welcome_email');

        // sendSms(auth()->user()->current_team_id, 'eeeee', '0201013001322');
        //    dd(Jawaly::getCredits());
    }


    public function preparePanelsPrint(Request $request, $type, $date)
    {
        $date_formatted = Carbon::parse($date)->format('Y/m/d');
        $data = '';
        $title = '';
        switch ($type) {
            case 'arrival':
                $title = __('Arrivals For Day') . ' ' . $date_formatted;
                $data = DB::table('reservations as r')
                    ->leftJoin('units as u', 'r.unit_id', '=', 'u.id')
                    ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
                    ->leftJoin('wallets as w', function ($join) {
                        $join->on('r.id', '=', 'w.holder_id')
                            ->where('w.holder_type', 'App\Reservation');
                    })
                    ->select(
                        'r.id as rid',
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
                        'w.decimal_places as decimal_places'
                    )
                    ->where('r.team_id', auth()->user()->current_team_id)
                    ->where('date_in', '=', $date)
                    ->whereIn('r.status', ['confirmed', 'awaiting-payment'])
                    ->whereNull('r.deleted_at')
                    ->orderBy('r.number', 'desc')
                    ->get()->toArray();
                break;

            case 'departure':
                $title = __('Departures For Day') . ' ' . $date_formatted;
                $data = DB::table('reservations as r')
                    ->leftJoin('units as u', 'r.unit_id', '=', 'u.id')
                    ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
                    ->leftJoin('wallets as w', function ($join) {
                        $join->on('r.id', '=', 'w.holder_id')
                            ->where('w.holder_type', 'App\Reservation');
                    })
                    ->select(
                        'r.id as rid',
                        'r.number as rnum',
                        'r.date_in as rdi',
                        'r.date_out as rdo',
                        'r.checked_in as rchi',
                        'r.checked_out as rcho',
                        'r.status',
                        'u.id as uid',
                        'u.unit_number as unum',
                        'c.id as cid',
                        'c.name as cname',
                        'c.phone as cphone',
                        'w.balance as rb',
                        'w.decimal_places as decimal_places'
                    )
                    ->where(function ($query) use ($date) {
                        $query->where('r.date_out', '=', $date)
                            ->whereNull('r.checked_out')
                            ->where('r.team_id', auth()->user()->current_team_id)
                            ->where('r.status', '=', 'confirmed')
                            ->whereNull('r.deleted_at');
                    })
                    ->orWhere(function ($query) use ($date) {
                        $query->whereDate('r.checked_out', $date)
                            ->where('r.team_id', auth()->user()->current_team_id)
                            ->where('r.status', '=', 'confirmed')
                            ->whereNull('r.deleted_at');
                    })
                    ->orderBy('r.number', 'desc')
                    ->get()->toArray();


                break;

            case 'departure-overdue':
                $title = __('Over Out Dates Customers');
                $day_end = Settings::get('day_end');
                $date = date('Y-m-d');
                $combinedDT = date('Y-m-d H:i:s', strtotime("$date $day_end"));
                $currentDate = Carbon::now('GMT+3')->format('Y-m-d');
                $data = DB::table('reservations as r')
                    ->leftJoin('units as u', 'r.unit_id', '=', 'u.id')
                    ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
                    ->leftJoin('wallets as w', function ($join) {
                        $join->on('r.id', '=', 'w.holder_id')
                            ->where('w.holder_type', 'App\Reservation');
                    })
                    ->select(
                        'r.id as rid',
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
                        'w.decimal_places as decimal_places'
                    )
                    ->whereNull('checked_out')
                    ->whereNotNull('checked_in')
                    ->where('date_out_time', '<=', $combinedDT)
                    ->where('date_out', '<', $currentDate)
                    ->where(['r.team_id' => auth()->user()->current_team_id])
                    ->where('r.status', '=', Reservation::STATUS_CONFIRMED)
                    ->whereNull('r.deleted_at')
                    ->orderBy('r.number', 'desc')
                    ->get()->toArray();

                break;
        }


        return view('print.panels-data', ['data' => $data, 'count' => count($data),  'title' => $title]);
    }
}
