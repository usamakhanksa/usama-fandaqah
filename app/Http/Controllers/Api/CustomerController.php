<?php

namespace App\Http\Controllers\Api;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerController extends Controller
{
    /**
    * get search method 
    * 
    * @return App\Http\Resources\TransactionResource 
    */
    public function search(Request $request)
    {
        $data = QueryBuilder::for(Customer::class)
            ->allowedFilters([
                // AllowedFilter::exact('code'),
                AllowedFilter::scope('by_search'),
            ])
            ->paginate($request->get('per_page', 30));

        return  CustomerResource::collection($data)->additional([
            'meta' => [
            ]
        ]);
    }

    /**
    * get reservation index method 
    * 
    * @return App\Http\Resources\ReservationResource 
    */
    public function types(Request $request)
    { 
        return ['data' => [
            1 => __('Male'),
            2 => __('Female'),
        ]];
    } 

    /**
    * get reservation index method 
    * 
    * @return App\Http\Resources\ReservationResource 
    */
    public function id_types(Request $request)
    { 
        return ['data' => Customer::idTypes()];
    } 
    
    /**
    * get reservation index method 
    * 
    * @return App\Http\Resources\ReservationResource 
    */
    public function purpose_of_visit(Request $request)
    { 
        return ['data' => Customer::purposeOfVisit()];
    }    

    /**
    * get reservation index method 
    * 
    * @return App\Http\Resources\ReservationResource 
    */
    public function customer_types(Request $request)
    { 
        return ['data' => Customer::customerTypes()];
    }

    /**
    * get reservation index method 
    * 
    * @return App\Http\Resources\ReservationResource 
    */
    public function relation_types(Request $request)
    {
        $relationships = [
            [
                'id' => 1,
                'name' => __('Son')
            ],     
            [
                'id' => 2,
                'name' => __('Daughter')
            ],
            [
                'id' => 3,
                'name' => __('Wife')
            ],
            [
                'id' => 4,
                'name' => __('Brother')
            ],
            [
                'id' => 5,
                'name' => __('Sister')
            ],
            [
                'id' => 6,
                'name' => __('Father')
            ],
            [
                'id' => 7,
                'name' => __('Mother')
            ],
            [
                'id' => 8,
                'name' => __('Husband')
            ],
            [
                'id' => 9,
                'name' => __('Other')
            ],
        ];
        return [
            'data' => $relationships
        ];
    }
}