<?php

namespace App\Jobs;

use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TeamDeleted implements ShouldQueue
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
     * @throws \Exception
     */
    public function handle()
    {
        $relations = getRelationships($this->team);

        /** @var \ReflectionMethod $relation */
        foreach ($relations as $relation) {
            $name = $relation->name;

            $this->team->$name()->delete();
        }

        $user = $this->team->owner;
        if ($user and !$user->hasTeams()) {
            $user->delete();
        }
    }
}
