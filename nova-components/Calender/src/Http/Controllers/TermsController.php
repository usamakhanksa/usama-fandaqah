<?php

namespace SureLab\Calender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Term;
use App\Unit;
use App\UnitCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class TermsController extends Controller
{


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTerms(Request $request)
    {
        $type = $request->get('type');
        return response()->json(Term::where(['type' => $type])->get()->toArray());
    }
    

    public function showCashReceipt()
    {
        return response()->json(Term::where(['type' => 2])->get()->toArray());
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function showPaymentVoucher()
    {

        return response()->json(Term::where(['type' => 1])->get()->toArray());
    }

    public function getTermDetails(Request $request)
    {
        $term_id = $request->get('id') ;
        $term = Term::find($term_id);
        return response()->json($term) ;
    }

}
