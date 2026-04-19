<?php

namespace SureLab\Calender\Http\Controllers;

use App\Team;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\StaahDailyReservation;
use App\Http\Controllers\Controller;

class ChannelManagerController extends Controller
{
    public function fetchReservations(Request $request)
    {
        $hotels_ids = [];
        $teams = Team::where('channel_manager_status', 'connected')->whereNull('deleted_at')->get();
        if (count($teams)) {
            $getAllProperties = $this->getPropertyListingFromStaah();

            if ($getAllProperties  && $getAllProperties->properties) {
                $hotels_ids = collect($getAllProperties->properties)->pluck('propertyid')->toArray();
            }

            foreach ($teams as $team) {
                if (in_array($team->id, $hotels_ids)) {
                    $body = ['hotelid' => $team->id];
                    $url = config('app.staah_mediator_api_url') . '/api/v1/reservation/list';
                    $client = new Client([
                        'headers' => [
                            'Content-Type' => 'application/json'
                        ]
                    ]);

                    try {
                        $request = $client->request('POST', $url, [
                            'body' => \GuzzleHttp\json_encode($body),
                            'exceptions' => true
                        ]);
                        $response = json_decode($request->getBody()->getContents());
                        if ($response && $response->reservations && !is_null($response->reservations)) {

                            $checkDailyReservation = StaahDailyReservation::where('team_id', $team->id)->first();
                            if ($checkDailyReservation) {
                                $checkDailyReservation->data = json_encode($response->reservations);
                                $checkDailyReservation->save();
                            } else {
                                $staah_daily_reservation = new StaahDailyReservation;
                                $staah_daily_reservation->team_id = $team->id;
                                $staah_daily_reservation->data = json_encode($response->reservations);
                                $staah_daily_reservation->save();
                            }

                            return response()->json(['success' => true]);
                        }
                    } catch (\Throwable $e) {
                        return response()->json(['success' => false]);
                        // logger('calling ' . config('app.staah_mediator_api_url') . '/api/v1/reservation/list' . ' resulted in error : ' . $e->getCode());
                    }
                }
            }
        }
        return response()->json('welcome');
    }

    function getPropertyListingFromStaah()
    {
        // 
        $url = config('app.staah_mediator_api_url') . '/api/v1/property/list';
        $client = new Client([
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        $request = $client->request('GET', $url, [
            'exceptions' => true
        ]);

        return json_decode($request->getBody()->getContents());
    }
}
