<?php

namespace App\Jobs;

use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TeamRestored implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Team */
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $relations = getRelationships($this->team);
        $owner = $this->team->owner()->withTrashed();
        $owner->restore();

        /** @var \ReflectionMethod $relation */
        foreach ($relations as $relation) {
            $name = $relation->name;
            $this->team->$name()->withTrashed()->restore();

//            if(method_exists($this->team->$name(), 'withTrashed')){
//                $this->team->$name()->withTrashed()->restore();
//            }
        }

        foreach ($this->team->users as $user) {
            if ($user and $user->trashed()) {
                $user->restore();
                $user->deleted_at = null;
                $user->save();
            }
        }
    }
}
