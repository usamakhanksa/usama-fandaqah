<?php

namespace Surelab\Settings\Events;

use Illuminate\Queue\SerializesModels;
use Surelab\Settings\ValueObjects\SettingRegister;

/**
 * Class SettingsRegistering
 * @package Surelab\Settings\Events
 */
final class SettingsRegistering
{
    use SerializesModels;

    /**
     * @var SettingRegister
     */
    public $settingRegister;

    /**
     * Create a new SettingRegister instance.
     *
     * @param  SettingRegister  $settingRegister
     * @return void
     */
    public function __construct(SettingRegister $settingRegister)
    {
        $this->settingRegister = $settingRegister;

    }
}
