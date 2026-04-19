<?php

namespace App\Observers;


use App\Term;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\DeleteTermException;
use Illuminate\Validation\ValidationException;

class TermObserver
{

    public function creating(Term $term)
    {
        // if type == Cash and Payment (3)
        if($term->type == 3){
            //craete Payment Voucher
            $term->type = 1; 

            //then craete Cash Receipt
            $term2 = clone $term;
            $term2->type = 2;
            $term2->save();
        }
    }

    public function created(Term $term)
    {
    }

    /**
     * Handle the term "updated" event.
     *
     * @param  \App\Term  $term
     * @return void
     */
    public function updated(Term $term)
    {
        //
    }

    
   public function deleting(Term $term) {
    
        if($term->transactions()->count()){
            throw new DeleteTermException(__('Term can not be deleted'));  
        }
       
   }
 

    /**
     * Handle the term "deleted" event.
     *
     * @param  \App\Term  $term
     * @return void
     */
    public function deleted(Term $term)
    {
        //
    }

    /**
     * Handle the term "restored" event.
     *
     * @param  \App\Term  $term
     * @return void
     */
    public function restored(Term $term)
    {
        //
    }

    /**
     * Handle the term "force deleted" event.
     *
     * @param  \App\Term  $term
     * @return void
     */
    public function forceDeleted(Term $term)
    {
        //
    }
}
