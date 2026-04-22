<?php
namespace App\Nova\Filters\Foundation;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class ReservationStatusFilter extends Filter
{
    public $name = 'Reservation Status';
    public function apply(Request $request, $query, $value){ return $query->where('status',$value); }
    public function options(Request $request): array { return ['Pending'=>'pending','Confirmed'=>'confirmed','Checked In'=>'checked_in','Checked Out'=>'checked_out','Cancelled'=>'cancelled']; }
}
