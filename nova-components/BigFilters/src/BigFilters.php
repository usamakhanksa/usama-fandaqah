<?php

namespace Surelab\BigFilters;

use Laravel\Nova\Card;
use NrmlCo\NovaBigFilter\NovaBigFilter;

class BigFilters extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'big-filters';
    }

    /**
     * Indicates that the analytics should show current visitors.
     *
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title = 'Filter Menu')
    {
        return $this->withMeta([
            'filterMenuTitle' => $title
        ]);
    }

    /**
     * Hide filter title (cause its obvious)
     *
     * @return $this
     */
    public function hideFilterTitle()
    {
        return $this->withMeta([
            'filterHideTitle' => true
        ]);
    }
}
