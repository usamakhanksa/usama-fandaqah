<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixFutureBirthDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:birthdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix all the future birthdates in customers table';

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
        return DB::table('customer')
            ->whereNotNull('birthday_date')
            ->whereDate('birthday_date', '>', Carbon::now()->toDateTime())
            ->orWhere('birthday_date', '0000-00-00')
            ->update(['birthday_date' => '1990-04-15']);
    }
}
