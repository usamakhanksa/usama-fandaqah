<?php

namespace App\Console\Commands;

use App\Customer;
use App\IdTypes;
use Illuminate\Console\Command;

class ImportCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:customers {team_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will dump customers under specific team into database';

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


        $current_team_id = $this->argument('team_id');
        $data = [];
        $file = fopen(asset('customers_complete.csv'), "r");

        while ( ($getData = fgetcsv($file, 100000, ",")) != FALSE) {
            $id_type = $getData[3] ;
            $checkType =  IdTypes::where('type_title' , 'LIKE' , "%$id_type")->first();

            $data [] = [
                'name'  =>  $getData[0],
                'id_number'  =>  $getData[1],
                'phone'  =>  $getData[2],
                'id_type'  => $checkType ? $checkType->type_id : null
            ];
        }
        fclose($file);
        foreach ($data as $customer) {
            Customer::create([
                'name' =>  $customer['name'],
                'phone' =>  $customer['phone'],
                'id_number' =>  $customer['id_number'],
                'id_type'   =>  $customer['id_type'],
                'team_id'   =>  $current_team_id
            ]);
        }
    }
}
