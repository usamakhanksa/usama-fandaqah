<?php
namespace App\Nova\Lenses\Foundation;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

class HighValueReservations extends Lens
{
    public static function query(LensRequest $request, Builder $query): Builder
    { return $request->withOrdering($request->withFilters($query->where('total_sar','>=',5000))); }
    public function fields(LensRequest $request): array
    { return [ID::make(),Text::make('Reservation','reservation_no'),Number::make('Total SAR','total_sar')]; }
}
