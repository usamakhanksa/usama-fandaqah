<?php

namespace App\Console\Commands;

use App\ServiceLog;
use Illuminate\Console\Command;

class ChangeServicesCategoriesName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:categories-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix service categories names reported by ahmed saied';

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
        $team_id = $this->ask('whats is ur team id?');
        $services = ServiceLog::with('transaction')->where('team_id', $team_id)->whereJsonContains('meta->statement', 'الغذاء ريف')->get();
        if (count($services)) {
            foreach ($services as $service) {
                // FOOD REV
                $new_meta = $service->meta;
                $new_meta['statement'] = 'FOOD REV';
                $service->meta = $new_meta;
                $service->save();
            }
        }
    }
}
