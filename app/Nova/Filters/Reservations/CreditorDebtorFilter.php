<?php

namespace App\Nova\Filters\Reservations;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class CreditorDebtorFilter extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'creditor-debtor-filter';
    public $name = 'Type of indebtedness' ;

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
        return $query->indebtednessType($value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [

            __('Creditor') => 'creditor',
            __('Debtor') => 'debtor'
        ];
    }
}
