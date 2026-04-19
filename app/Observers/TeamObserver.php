<?php

namespace App\Observers;

use App\Jobs\DefaultNotificationControlSettings;
use App\Jobs\SeedTeamDefaultSettings;
use App\Notifications\RegistrationWelcomeNotification;
use App\Team;
use Illuminate\Support\Facades\Auth;

class TeamObserver
{
    public function creating(Team $team)
    {

    }

    public function created(Team $team)
    {
        SeedTeamDefaultSettings::dispatch($team);
        DefaultNotificationControlSettings::dispatch($team);
    }

    /**
     * Handle the team "updated" event.
     *
     * @param  \App\Team  $team
     * @return void
     */
    public function updated(Team $team)
    {
        //
    }

    /**
     * Handle the team "deleted" event.
     *
     * @param  \App\Team  $team
     * @return void
     */
    public function deleted(Team $team)
    {
        //
    }

    /**
     * Handle the team "restored" event.
     *
     * @param  \App\Team  $team
     * @return void
     */
    public function restored(Team $team)
    {
        //
    }

    /**
     * Handle the team "force deleted" event.
     *
     * @param  \App\Team  $team
     * @return void
     */
    public function forceDeleted(Team $team)
    {
        //
    }
}
