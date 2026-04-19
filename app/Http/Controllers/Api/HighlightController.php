<?php

namespace App\Http\Controllers\Api;

use App\Highlight;
use App\Http\Controllers\Controller;
use App\Http\Resources\HighlightResource;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class HighlightController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = QueryBuilder::for(Highlight::class)
            ->allowedFilters([
                // AllowedFilter::scope('status'),
            ])
            ->defaultSort('-order')
            ->where(['status' => true])
            ->where('team_id', $request->get('current_team_id'))
            ->allowedSorts('id', 'order')
            ->get();
        return  HighlightResource::collection($data);
    }
}