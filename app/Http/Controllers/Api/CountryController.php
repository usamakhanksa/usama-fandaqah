<?php

namespace App\Http\Controllers\Api;

use App\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CountryController extends Controller
{
    /**
    * get transaction index method 
    * 
    * @return App\Http\Resources\TransactionResource 
    */
    public function index(Request $request)
    { 
        $transactions = QueryBuilder::for(Country::class)
            ->allowedIncludes(['customer'])
            ->allowedFilters([
                AllowedFilter::exact('code')
            ])
            ->paginate($request->get('per_page', 2000));

        return  CountryResource::collection($transactions)->additional([
            'meta' => [
            ]
        ]);
    }
}