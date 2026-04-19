<?php

namespace Surelab\CustomerNotes;
use Laravel\Nova\ResourceTool;

class CustomerNotes extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Customer Notes';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'customer-notes';
    }

}
