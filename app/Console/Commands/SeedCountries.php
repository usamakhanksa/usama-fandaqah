<?php

namespace App\Console\Commands;

use App\Country;
use App\Imports\CountriesImport;
use App\Occupied;
use App\Role;
use App\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SeedCountries extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Countries';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $collection = Excel::toCollection(new CountriesImport, storage_path('countries.xlsx'))[3];
        $collection->shift();

        foreach ($collection->all() as $value) {
            $country = new Country;
            $country->code = $value[0];
            $country->is_gcc = in_array($value[0], [108,106,103,109,101,113 ]);
            $country->setTranslation('title', 'ar', $value[1]);
            $country->setTranslation('title', 'en', $value[2]);
            $country->save();
        }
        
    }
}
