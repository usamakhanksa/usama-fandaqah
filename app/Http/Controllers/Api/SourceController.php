<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Source;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SourceController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = QueryBuilder::for(Source::class)
            ->allowedFilters([
                // AllowedFilter::scope('status'),
            ])
            ->defaultSort('-order')
            ->where(['status' => true])
            ->where('team_id', $request->get('current_team_id'))
            ->allowedSorts('id', 'order')
            ->get();
        return  SourceResource::collection($data);
    }
}