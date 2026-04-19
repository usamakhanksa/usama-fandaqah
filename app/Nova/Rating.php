<?php
/**
 *  * Created by PhpStorm.
 * User: Mohamed Yasser ( yasoesr@gmail.com )
 * Date: 12/3/19
 * Time: 1:32 PM
 */

namespace App\Nova;

use App\Nova\Filters\Evaluation\DateFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Rating extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Rating';

    /**
     * Get the displayable label of the resource.
     *
     * @return  string
     */
    public static function label()
    {
        return __('Customer Ratings');
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }

    public static function searchable()
    {
        return false;
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return  string
     */
    public static function singularLabel()
    {
        return __('Customer Rating');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Number::make('rating_value'),
            Number::make('q_one'),
            Number::make('q_two'),
            Number::make('q_three'),
            Number::make('q_four'),
            Number::make('q_five'),
            Number::make('q_six'),
            Text::make('q_seven'),
            Text::make('q_eight'),
            Number::make('reservation_number'),
            Text::make('reservation_date_in'),
            Text::make('reservation_date_out'),
            Text::make('reservation_checked_in'),
            Text::make('reservation_checked_out'),
            Text::make('customer_name'),
            Number::make('customer_id'),
            Number::make('unit_number'),
            Number::make('unit_id'),
            Number::make('nights'),
            DateTime::make('created_at'),
            BelongsTo::make('reservation')
        ];
    }

    public function filters(Request $request)
    {
        return [
            new DateFilter
        ];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->where('team_id', '=', \Auth::user()->current_team_id);

        return $query;
    }
}
