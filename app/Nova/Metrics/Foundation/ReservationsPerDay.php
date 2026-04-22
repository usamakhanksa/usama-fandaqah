<?php
namespace App\Nova\Metrics\Foundation;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;

class ReservationsPerDay extends Trend
{
    public function calculate(NovaRequest $request){ return $this->countByDays($request, \App\Models\Foundation\Reservation::class); }
    public function ranges(): array { return [7=>__('7 Days'),30=>__('30 Days'),60=>__('60 Days')]; }
    public function uriKey(): string { return 'reservations-per-day'; }
}
