<?php

namespace App\Console\Commands;

use App\Team;
use Themsaid\Forge\Forge;
use Illuminate\Console\Command;

class WebsiteInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the newly created website for customer website private domain';

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
        $teams = Team::newPrivateDomains('installing')->get();

        if ($teams->count() < 1) {
            return false;
        }

        // forge connection
        $forge = new Forge(env('FORGE_PRIVATE_DOMAIN_API_TOKEN'));
        $sites = $forge->sites(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'));
        $sites = collect($sites);

        // update nginx and install ssl certificate for every domain
        foreach ($teams as $team) {

            // get current site
            $site = $sites->first(function ($item, $key) use($team) {
                return $item->name == $team->private_domain;
            });

            // return if not installed on forge
            if (!$site || $site->status != 'installed') {
                return false;
            }

            // nginx configurations
            $search_for = 'root /home/forge/' . $site->name;
            $replace_with = 'root ' . env('FORGE_PRIVATE_BASE_PATH');
            $content = $forge->siteNginxFile(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'), $site->id);
            if (strpos($content, $replace_with) === false) {
                $content = str_replace($search_for, $replace_with, $content);
                $forge->updateSiteNginxFile(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'), $site->id, $content);
            }

            // create ssl certificate if not exist
            $certificates = $forge->certificates(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'), $site->id);
            if (count($certificates) < 1) {
                return $forge->obtainLetsEncryptCertificate(
                    env('FORGE_PRIVATE_DOMAIN_SERVER_ID'),
                    $site->id,
                    ['domains' => [$site->name]],
                    false
                );
            }

            // if not installed return
            if ($certificates[0]->status != 'installed') {
                return false;
            }

            // activate ssl if not activated
            if ($certificates[0]->activationStatus != 'activated') {
                return $forge->activateCertificate(env('FORGE_PRIVATE_DOMAIN_SERVER_ID'), $site->id, $certificate->id, false);
            }

            // set status to installed
            $team->private_domain_status = 'installed';
            $team->save();

            // update sure bills redirect url
            // $team->updateBillsAccountRedirectUrls();
        }
    }
}
