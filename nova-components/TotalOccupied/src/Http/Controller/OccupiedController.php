<?php

namespace Surelab\TotalOccupied\Http\Controller;

use App\Http\Controllers\Controller;
use App\Occupied;
use Carbon\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;

class OccupiedController extends Controller
{
    public function getTotalAction(NovaRequest $request)
    {
        $dates = null;
        $query = Occupied::query();
        $filters = json_decode(base64_decode(\request('filters')), true);
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if (!is_null($filter['value']) and !empty($filter['value'])) {
                    $dates = $filter['value'];
                    (new $filter['class'])->apply($request, $query, $filter['value']);
                }
            }
        }

        $countResults = count($query->get());

        if (isset($dates[0]) and isset($dates[1])) {
            $from = Carbon::parse($dates[0]);
            $to = Carbon::parse($dates[1]);
            $diffInDays = $to->diffInDays($from);
            $query = \App\Nova\Occupied::indexQuery($request,$query);
            $query->whereNotNull('team_id')->where('team_id', auth()->user()->current_team_id);
            $sum = $query->sum('percentage');
            // am adding one cause diffInDays from 12 to 13 will give you 1 day only , however they are 2
            $diff = $diffInDays <= 0 ? 1 : $diffInDays + 1 ;
            return response()->json(['total'    =>  number_format($sum/$countResults, 2)]);
        } else {
            $occupieds = Occupied::query()->pluck('created_at')->toArray();
            $min = min($occupieds);
            $max = max($occupieds);
            $diffInDays = $max->diffInDays($min);
            $sum = Occupied::query()->sum('percentage');
            $diff = $diffInDays <= 0 ? 1 : $diffInDays + 1 ;
            return response()->json(['total'    =>  number_format($sum/$countResults, 2)]);
        }




    }
}
