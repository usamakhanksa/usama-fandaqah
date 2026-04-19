<?php

namespace App\Console\Commands;

use App\Country;
use App\Events\ReservationUpdated;
use App\Events\ShomosActionCreated;
use App\Handlers\Settings;
use App\IntegrationLog;
use App\Integration\SHMS;
use App\Jobs\SCTH\ExpenseBooking;
use App\Jobs\SHMS\CheckIn;
use App\Reservation;
use App\Transaction;
use App\Video;
use Faker\Factory as Faker;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class ShomosHistory extends Command
{

    const STATUS = [
        0 => 'N/A',
        1 => 'validation',
        2 => 'exception',
        3 => 'done',
    ];

   protected  $actions = [
        'check_in' => 1,
        'update_check_in' => 2,
        'check_out' => 3,
        'check_out_guest' => 7,
        'transfer' => 8,
        'edit_room' => 9,
    ];

   protected $client;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shomos:history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get shomos history';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(); 
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getData();
    }


    protected function getData($url='https://shomoos.fandaqah.com/logs/all?per_page=20&less_than_date=2020-08-23')
    {
        $response = $this->client->get($url, [
            'form_params' => [
                'sample-form-data' => 'value'
            ]
        ]);
        $data = collect(json_decode($response->getBody()->getContents(), true));
        $this->error('count '.count($data['data']));
        foreach ($data['data'] as $item) {
            if(isset($item['model_data']['id']) &&  isset($item['model_data']['team_id'])){
                $this->info('log id: '.$item['id'] . ' reservation id: '.$item['model_data']['id']);
                $reservation = Reservation::find($item['model_data']['id']);
                if($reservation){
                    $log = new IntegrationLog();
                    $log->team_id = $item['model_data']['team_id'];
                    $log->type = SHMS::class;
                    $log->action = $this->actions[$item['type']];
                    $log->payload = $item['model_data'];
                    $log->response = $item['response'];
                    $log->status = $item['status'] == 3 ? 1:2;
                    $log->save();

                    event(new ShomosActionCreated($reservation, $log->status));
                }
            }
        }
        $this->getData($data['next_page_url'].'&per_page='.$data['per_page'].'&less_than_date=2020-08-23');
    }

}
