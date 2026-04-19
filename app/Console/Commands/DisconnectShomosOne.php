<?php

namespace App\Console\Commands;

use App\Integration;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DisconnectShomosOne extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disconnect:shms-1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disconnect shomos v1 integrgation';

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
        $shms_v1_integrations = Integration::where('key' , 'SHMS')
        ->whereJsonContains('values->SPSCID', null)
        ->whereNull('deleted_at')
        ->get();

        if(count($shms_v1_integrations)){
            foreach ($shms_v1_integrations as $shms_one) {
                $shms_one->deleted_at = Carbon::now();
                $shms_one->save();
            }
        }
    }
}
