<?php

namespace App\Observers;

use App\Setting;

class SettingObserver
{
    /**
     * Handle the setting "created" event.
     *
     * @param  \App\Setting  $setting
     * @return void
     */
    public function created(Setting $setting)
    {
        cache()->forget('settings_' . $setting->key . '_' . $setting->team_id);
    }

    /**
     * Handle the setting "updated" event.
     *
     * @param  \App\Setting  $setting
     * @return void
     */
    public function updated(Setting $setting)
    {
        cache()->forget('settings_' . $setting->key . '_' . $setting->team_id);
    }

    /**
     * Handle the setting "deleted" event.
     *
     * @param  \App\Setting  $setting
     * @return void
     */
    public function deleted(Setting $setting)
    {
        cache()->forget('settings_' . $setting->key . '_' . $setting->team_id);
    }

    /**
     * Handle the setting "restored" event.
     *
     * @param  \App\Setting  $setting
     * @return void
     */
    public function restored(Setting $setting)
    {
        cache()->forget('settings_' . $setting->key . '_' . $setting->team_id);
    }

    /**
     * Handle the setting "force deleted" event.
     *
     * @param  \App\Setting  $setting
     * @return void
     */
    public function forceDeleted(Setting $setting)
    {
        cache()->forget('settings_' . $setting->key . '_' . $setting->team_id);
    }
}
