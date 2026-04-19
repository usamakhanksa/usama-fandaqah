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

class OccupancyUpdate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 1;
    protected $team;
    protected $transaction_id;
    protected $dayClosing;
    protected $credentials;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Team $team, $transaction_id = null, $dayClosing = false)
    {
        $this->team = $team;
        $this->transaction_id = $transaction_id;
        $this->dayClosing = $dayClosing;
        $this->credentials = Settings::checkIntegration('SCTH', $this->team->id);

    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $occupanyUpdate = SCTH::occupancy($this->team, $this->credentials, 2.0, $this->transaction_id, $this->dayClosing);
        // Log::info($occupanyUpdate);
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
        // Mail::to(explode(',', env('JOBS_FAILURE_NOTIFIER_EMAILS')))->send(new ScthJobFailed($this->team, $exception->getMessage(), Carbon::now()->format('Y-m-d h:i A'), OccupancyUpdate::class));
    }
}
