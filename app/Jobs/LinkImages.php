<?php
namespace App\Jobs;

use App\Models\WebsiteSetting; 
use App\Models\WebsiteGallery;
use App\Models\Team;
use Illuminate\Bus\Queueable;
// use App\QueueResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LinkImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3;
    protected $jobData;

    public function __construct($jobData)
    {
        $this->jobData = $jobData;
    }

    public function handle()
    {
        $photo_types = $this->jobData['photo_types'];

        $curl = curl_init();
        $url = env('MYTRAVEL_API_URL') . '/api/fandaqahImage/store';
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
            CURLOPT_POSTFIELDS => json_encode($photo_types),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'key: ' . $key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        echo $response;

    }
}
