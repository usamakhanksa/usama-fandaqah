<?php

namespace Surelab\Settings\Exceptions;

use Exception;

/**
 * Class SettingTypeNotValidException
 * @package Surelab\Settings\Exceptions
 */
final class SettingTypeNotValidException extends Exception
{
    /**
     * SettingTypeNotValidException constructor.
     * @param string $inputType
     */
    public function __construct(string $inputType = "")
    {
        parent::__construct("`{$inputType}` is not a valid SettingType.", 0, null);
    }
}
