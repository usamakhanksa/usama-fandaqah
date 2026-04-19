<?php

namespace App\Console\Commands;

use App\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateServiceTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:service-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be update the meta column inside transactions table to set default indexes needed for new service logic';

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
        if ($this->confirm('Do you want to update all services transactions to be formed as the newly object structure  ?')) {

            // we need to fetch all transactions , then iterate through each one to find each object then update it's skeleton

            $transactions = Transaction::where('is_public' , 0)->whereJsonContains('meta->category' , 'service')->get();

            if(count($transactions)){
                $total_transactions_updated = 0  ;

                foreach ($transactions as $transaction){

                    if(!isset($transaction->meta['services'])){
                        $total_transactions_updated++ ;

                        // This array will be pushed to services index later in the new object
                        $services_arr = []  ;
                        // For service skeleton object inside services index
                        $serviceObj = new \stdClass() ;
                        $serviceObj->id = 1 ;
                        $serviceObj->category = 'service' ;
                        $serviceObj->statement = $transaction->meta['statement'];
                        $serviceObj->qty = $transaction->meta['qty'];
                        $serviceObj->vat =  0 ;
                        $serviceObj->ttx =  0 ;
                        $serviceObj->vatIsChecked =  false ;
                        $serviceObj->ttxIsChecked =  false ;
                        $serviceObj->price = number_format(abs($transaction->amount / 100) / $transaction->meta['qty'] , 2);
                        $serviceObj->sub_total = number_format(abs($transaction->amount / 100),2);
                        $serviceObj->totalGeneralSum =  $serviceObj->sub_total ;
                        $services_arr [] = $serviceObj ;

                        // This for new object structure after we update it to our new skeleton
                        $newObjectSkeleton = new \stdClass() ;
                        $newObjectSkeleton->qty = $transaction->meta['qty'];
                        $newObjectSkeleton->category = 'service' ;
                        $newObjectSkeleton->services =  $services_arr ;
                        $newObjectSkeleton->statement = __('Services') ;
                        $newObjectSkeleton->sub_total =  number_format(abs($transaction->amount / 100),2); ;
                        $newObjectSkeleton->ttx_total =  0 ;
                        $newObjectSkeleton->vat_total =  0 ;
                        $newObjectSkeleton->total_with_taxes = $newObjectSkeleton->sub_total  ;

                        // in this point when know that it's an old way object that needs to be modified
                        $transaction->meta = $newObjectSkeleton ;

                        $transaction->save();
                    }



                }
                if($total_transactions_updated > 0){
                    $this->info('Congratulations , all '. $total_transactions_updated .' services transactions meta object has been updated successfully');
                }else{
                    $this->info('No need to run this command , all services transactions meta object has now the new skeleton and well formed');
                }

            }else{
                $this->info('No services transactions has been added yet');
            }

        }else{
            $this->info('Thanks and GodBye -_-');
        }
    }
}
