<?php

namespace App\Nova\Metrics;

use App\Occupied;
use Illuminate\Http\Request;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;

class OccupiedPerDay extends Trend
{


    /**
     * Calculate the value of the metric.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function calculate(Request $request)
    {
        return (new TrendResult)->trend(
            Occupied::all()->mapWithKeys(function ($item) {
                return [$item->created_at->format('M d Y') => $item->occupied];
            })->toArray()
        );
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges()
    {
        return [
            1 => '1 ' . __('DAY'),
            2 => '2 '. __('DAY'),
            3 => '3 '. __('DAY'),
            4 => '4 '. __('DAY'),
            90 => '90 '. __('DAY'),
        ];
    }

    /**
     * Determine for how many minutes the metric should be cached.
     *
     * @return  \DateTimeInterface|\DateInterval|float|int
     */
    public function cacheFor()
    {
        // return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey()
    {
        return 'occupied-per-day';
    }

    // Set the name of the metrics
    public function name()
    {
        return __('OccupiedPerDay');
    }
}
