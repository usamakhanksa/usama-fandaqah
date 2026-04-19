<?php

namespace App\Observers;

use Surelab\Settings\Entities\SettingValue;

class SettingValueObserver
{
    /**
     * Handle the setting value "created" event.
     *
     * @param  \App\SettingValue  $settingValue
     * @return void
     */
    public function created(SettingValue $settingValue)
    {
        cache()->forget('settings_' . $settingValue->key . '_' . $settingValue->team_id);
    }

    /**
     * Handle the setting value "updated" event.
     *
     * @param  \App\SettingValue  $settingValue
     * @return void
     */
    public function updated(SettingValue $settingValue)
    {
        cache()->forget('settings_' . $settingValue->key . '_' . $settingValue->team_id);
    }

    /**
     * Handle the setting value "deleted" event.
     *
     * @param  \App\SettingValue  $settingValue
     * @return void
     */
    public function deleted(SettingValue $settingValue)
    {
        cache()->forget('settings_' . $settingValue->key . '_' . $settingValue->team_id);
    }

    /**
     * Handle the setting value "restored" event.
     *
     * @param  \App\SettingValue  $settingValue
     * @return void
     */
    public function restored(SettingValue $settingValue)
    {
        cache()->forget('settings_' . $settingValue->key . '_' . $settingValue->team_id);
    }

    /**
     * Handle the setting value "force deleted" event.
     *
     * @param  \App\SettingValue  $settingValue
     * @return void
     */
    public function forceDeleted(SettingValue $settingValue)
    {
        cache()->forget('settings_' . $settingValue->key . '_' . $settingValue->team_id);
    }
}
