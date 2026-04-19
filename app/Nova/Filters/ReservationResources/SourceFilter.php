<?php

namespace App\Nova\Filters;

use App\Source;
use App\User;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class SourceFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = "Source" ;

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->bySource($value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $current_team_id = auth()->user()->current_team_id ;
        $resources = Source::where(['team_id' => $current_team_id  , 'status' => 1])->get() ;
        $arr = [] ;
        foreach($resources as $source){
           $arr [] = [
               'name' => $source->name ,
               'value' => $source->id
           ];
        }
        return $arr ;

    }
}
