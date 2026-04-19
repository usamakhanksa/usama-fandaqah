<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TermResource;
use App\Reservation;
use App\Term;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TermController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function receipt(Request $request)
    {
        $data = QueryBuilder::for(Term::class)
            ->allowedFilters([
                // AllowedFilter::scope('status'),
            ])
            ->defaultSort('-order')
            ->where(['type' => 2, 'status' => true])
            // ->where('team_id', $request->get('current_team_id'))
            ->allowedSorts('id', 'order')
            ->get();
        return  TermResource::collection($data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentVoucher(Request $request)
    {
        $data = QueryBuilder::for(Term::class)
            ->allowedFilters([
                // AllowedFilter::scope('status'),
            ])
            ->defaultSort('-order')
            ->where(['type' => 1, 'status' => true])
            // ->where('team_id', $request->get('current_team_id'))
            ->allowedSorts('id', 'order')
            ->get();
        return  TermResource::collection($data);
    }
}