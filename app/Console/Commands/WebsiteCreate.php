<?php

namespace App\Console\Commands;

use App\Team;
use Themsaid\Forge\Forge;
use Illuminate\Console\Command;

class WebsiteCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new website for customer website private domain';

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
        $teams = Team::newPrivateDomains()->get();

        if ($teams->count() < 1) {
            return false;
        }

        // forge connection
        $forge = new Forge(env('FORGE_PRIVATE_DOMAIN_API_TOKEN'));
        $sites = $forge->sites(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'));
        $sites = collect($sites);

        // create a new website for evey domain
        foreach ($teams as $team) {

            // get current site
            $site = $sites->first(function ($item, $key) use($team) {
                return $item->name == $team->private_domain;
            });

            // create website if not exist
            if (!$site) {
                $forge->createSite(
                    env('FORGE_PRIVATE_DOMAIN_SERVER_ID'),
                    [
                        'domain'       => $team->private_domain,
                        'project_type' => 'php'
                    ],
                    false
                );
            }

            // set status to installing
            $team->private_domain_status = 'installing';
            $team->save();
        }
    }
}
