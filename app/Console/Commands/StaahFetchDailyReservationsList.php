<?php

namespace App\Console\Commands;

use App\Team;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\StaahDailyReservation;
use Illuminate\Console\Command;

class StaahFetchDailyReservationsList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'staah:fetch-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will call our staah mediator and fetch reservations list from it';
    protected $hotels_ids = [];
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $teams = Team::where('channel_manager_status', 'connected')->whereNull('deleted_at')->get();
        if (count($teams)) {
            $getAllProperties = $this->getPropertyListingFromStaah();

            if ($getAllProperties  && $getAllProperties->properties) {
                $this->hotels_ids = collect($getAllProperties->properties)->pluck('propertyid')->toArray();
            }
            foreach ($teams as $team) {
                if (in_array($team->id, $this->hotels_ids)) {
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
                        }
                    } catch (\Throwable $e) {
                        logger('calling ' . config('app.staah_mediator_api_url') . '/api/v1/reservation/list' . ' resulted in error : ' . $e->getCode());
                    }
                }
            }
        }
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
