<?php

namespace App\Jobs\SCTH;

use Exception;
use Carbon\Carbon;
use App\Reservation;
use App\Integration\SCTH;
use App\Mail\ScthJobFailed;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $reservation;
    protected $credentials;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, $credentials)
    {
        $this->reservation = $reservation;
        $this->credentials = $credentials;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $createBooking = SCTH::create($this->reservation, $this->credentials);
        // Log::info($createBooking);
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
        // Mail::to(explode(',', env('JOBS_FAILURE_NOTIFIER_EMAILS')))->send(new ScthJobFailed($this->reservation->team, $exception->getMessage(), Carbon::now()->format('Y-m-d h:i A') , CreateBooking::class));
    }
}
