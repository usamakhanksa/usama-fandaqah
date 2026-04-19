<?php

namespace App\Console\Commands;

use App\Team;
use Themsaid\Forge\Forge;
use Illuminate\Console\Command;

class WebsiteDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the existing website for customer website private domain';

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
        $teams = Team::deletedPrivateDomains()->get();

        if ($teams->count() < 1) {
            return false;
        }

        // forge connection
        $forge = new Forge(env('FORGE_PRIVATE_DOMAIN_API_TOKEN'));
        $sites = $forge->sites(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'));
        $sites = collect($sites);

        // delete every deleted domain from forge
        foreach ($teams as $team) {

            // get current site
            $site = $sites->first(function ($item, $key) use($team) {
                return $item->name == $team->private_domain;
            });

            // delete if exist
            if ($site) {
                return $forge->deleteSite(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'), $site->id);
            }

            // clear data
            $team->private_domain = null;
            $team->private_domain_status = null;
            $team->save();

            // update sure bills redirect url
            // $team->updateBillsAccountRedirectUrls();
        }
    }
}
