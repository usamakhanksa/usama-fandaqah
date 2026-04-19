<?php

namespace App\Nova\Filters;

use App\Highlight;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class CustomerHighlight extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    public $name = 'Highlight';

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
        return $query->where('highlight_id' , '=' , $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $highlights = Highlight::all();
        $filtered = [];
        foreach ($highlights as $highlight) {
            $filtered[] =[
                'name' => $highlight->name ,
                'value' => $highlight->id
            ];
        }

        return $filtered;
    }
}
