<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
// use App\QueueResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRoomImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries = 3; 
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $mainImageUrl = $this->data['main_image_url'];
        $galleryUrls = $this->data['gallery'];
        $categoryId = $this->data['category_id'];

        $data = [
            'main_image_url' => $mainImageUrl,
            'gallery' => $galleryUrls,
            'category_id' => $categoryId,
        ];

        $curl = curl_init();
        $url = env('MYTRAVEL_API_URL') . '/api/fandaqahRoomImage/store';
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
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'key: ' . $key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        echo $response;
        // QueueResponse::create([
        //     'job_id' => $this->job->getJobId(),
        //     'response' => $response,
        //     'status' => 'completed', // or any other status you want to track
        // ]);
        return $response;

    }
}

