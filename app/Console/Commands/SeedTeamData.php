<?php

namespace App\Console\Commands;

use App\Jobs\SeedTeamDefaultSettings;
use App\Team;
use App\User;
use Illuminate\Console\Command;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\AddTeamMember as AddTeamMemberContract;
use Laravel\Spark\Events\Teams\TeamOwnerAdded;
use Laravel\Spark\Spark;

class SeedTeamData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'team:seed {team}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Team Default Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $team = Team::find($this->argument('team'));

        $housekeeping = new \App\Role();
        $housekeeping->name = 'Housekeeping';
        $housekeeping->slug = 'housekeeping';
        $housekeeping->team_id = $team->id;
        $housekeeping->deletable = 0;
        $housekeeping->save();

        $staff = new \App\Role();
        $staff->name = 'Staff';
        $staff->slug = 'staff';
        $staff->team_id = $team->id;
        $staff->deletable = 0;
        $staff->save();

        $role = new \App\Role();
        $role->name = 'Admin';
        $role->slug = 'admin';
        $role->deletable = 0;
        $role->team_id = $team->id;

        $role->save();
        foreach (config('novapermissions.permissions') as $key => $permissions) {
            $role->grant($key);
        }

        $user = User::find($team->owner_id);
        $user->roles()->attach($role);

        SeedTeamDefaultSettings::dispatch($team);

        $this->info('Thanks and GodBye');
    }
}
