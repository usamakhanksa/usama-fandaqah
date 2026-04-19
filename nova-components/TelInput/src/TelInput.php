<?php

namespace Surelab\TelInput;

use Laravel\Nova\Fields\Field;

class TelInput extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'tel-input';

    /**
     * Tells VueJS to load only masks from the given countries.
     *
     * @param mixed ...$countries
     * @return TelInput
     */
    public function onlyCountries(...$countries)
    {
        return $this->withMeta([
            'onlyCountries' => array_flatten($countries),
        ]);
    }

    /**
     * Tells VueJS to load only masks from the given countries.
     *
     * @param mixed ...$countries
     * @return TelInput
     */
    public function preferredCountries(...$countries)
    {
        return $this->withMeta([
            'onlyCountries' => array_flatten($countries),
        ]);
    }

    /**
     * Tells VueJS to load only masks from the given countries.
     *
     * @param string $country
     * @return TelInput
     */
    public function defaultCountry($country)
    {
        return $this->withMeta([
            'defaultCountry' => $country,
        ]);
    }


}
