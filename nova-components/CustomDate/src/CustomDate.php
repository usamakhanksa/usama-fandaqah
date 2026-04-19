<?php

namespace Surelab\CustomDate;

use Laravel\Nova\Fields\Field;

class CustomDate extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'custom-date';

    /**
     * Set the first day of the week.
     *
     * @param  int  $day
     * @return $this
     */
    public function firstDayOfWeek($day)
    {
        return $this->withMeta([__FUNCTION__ => $day]);
    }

    /**
     * Set the date format (Moment.js) that should be used to display the date.
     *
     * @param  string  $format
     * @return $this
     */
    public function format($format)
    {
        return $this->withMeta([__FUNCTION__ => $format]);
    }
}
