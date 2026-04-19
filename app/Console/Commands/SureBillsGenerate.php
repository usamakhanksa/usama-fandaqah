<?php

namespace App\Console\Commands;

use App\IntegrationSettings;
use App\Jobs\SeedTeamDefaultSettings;
use App\Team;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\AddTeamMember as AddTeamMemberContract;
use Laravel\Spark\Events\Teams\TeamOwnerAdded;
use Laravel\Spark\Spark;

class SureBillsGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surebills:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed  Sure Bills';

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
        if(!IntegrationSettings::find('4')){
            $row = "INSERT INTO `integration_settings` (`id`, `name`, `description`, `logo`, `url`, `key`, `fields`, `order`, `status`, `created_at`, `updated_at`)
                VALUES
                    (4, '{\"ar\": \"شور للفواتير\", \"en\": \"Sure Bills\"}', '{\"ar\": \"شور للفواتير\", \"en\": \"Sure Bills\"}', 'https://bills.surepay.sa/images/logo-black.svg', 'https://bills.surepay.sa/', 'SureBills', '{\"client_id\": null, \"secret\": null, \"webhook_url\": null, \"webhook_secret\": null}', 3, 1, NULL, NULL)";

            DB::select($row);
            $this->info('seed Sure Bills succesfully');
        }else{
            $this->info('Sure Bills is exists ');

        }

    }
}
