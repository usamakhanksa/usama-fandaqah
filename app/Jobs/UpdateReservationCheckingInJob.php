<?php

namespace App\Jobs;

use App\Team;
use App\User;
use Carbon\Carbon;
use App\Reservation;
use Illuminate\Bus\Queueable;
use App\Broadcasting\SMSChannel;
use Illuminate\Queue\SerializesModels;
use App\Notifications\DailyBriefReport;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;

class UpdateReservationCheckingInJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $team_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($team_id = null)
    {
        $this->team_id = $team_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $day = Carbon::today()->subDay();
        $reservations = Reservation::setEagerLoads([])
        ->whereIntersectsDateIn($day)
        ->whereIntersectsDateOut($day)
        ->where('status', 'confirmed')
        ->whereNotNull('checked_in')
        ->whereNull('checked_out')
        ->where('checking_in',true)
        ->get();
        if(count($reservations)){
            foreach ($reservations as $reservation) {
                $reservation->checking_in = 0;
                $reservation->save();
            }
        }
    }
}
