<?php
/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 12/4/19
 * Time: 1:01 PM
 */

namespace App\Nova\Filters\Evaluation;

use Ampeco\Filters\DateRangeFilter;
use App\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DateFilter extends DateRangeFilter
{
    public $name = 'Date In';
    public $component = 'rating-date-range-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        $ratings = Reservation::has('rating')->whereBetween('checked_in', [$value[0], $value[1]])->pluck('rating_id');

        return $query->whereIn( 'id', $ratings);
    }
}
