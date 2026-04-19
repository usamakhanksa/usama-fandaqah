<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
// use App\QueueResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncRoomData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;

    protected $requestData;
    protected $url;
    protected $key;

    public function __construct($requestData, $url, $key)
    {
        $this->requestData = $requestData;
        $this->url = $url;
        $this->key = $key;
    }

    public function handle()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 400,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($this->requestData),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'key: ' . $this->key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // QueueResponse::create([
        //     'job_id' => $this->job->getJobId(),
        //     'response' => $response,
        //     'status' => 'completed', // or any other status you want to track
        // ]);

        // Handle the response as needed...
    }
}
