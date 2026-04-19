<?php

namespace App\Jobs\SCTH;

use App\Team;
use Exception;
use Carbon\Carbon;
use App\Integration\SCTH;
use App\Handlers\Settings;
use App\Mail\ScthJobFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScthDayClosingOccupancyUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    protected $team;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Team $team)
    {
        $this->team = $team;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $credentials = Settings::checkIntegration('SCTH', $this->team->id);
        if($credentials){
            $occupanyUpdate = SCTH::occupancy($this->team, $credentials,2.0,null,true);
            // Log::info($occupanyUpdate);
        }

    }

     /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // send email on failed jobs (in-progress)
        // Send user notification of failure, etc...
        // Mail::to(explode(',', env('JOBS_FAILURE_NOTIFIER_EMAILS')))->send(new ScthJobFailed($this->team , $exception->getMessage(), Carbon::now()->format('Y-m-d h:i A') , OccupancyUpdate::class));
    }
}
