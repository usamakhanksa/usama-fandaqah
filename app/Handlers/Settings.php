<?php


namespace App\Handlers;

use App\Integration;
use App\Setting;

/**
 * Class Settings
 * @package App\Handlers
 */
class Settings
{


    /**
     * Get Setting Value
     * @param $name
     * @return mixed|null
     */
    public static function get($name)
    {
        $meta = \Surelab\Settings\ValueObjects\SettingRegister::getSettingItem($name);
        if ($meta && $meta->getValue())
            return $meta->getValue();
        else
            return null;
    }


    /**
     * update Counter in settings
     * @param $name
     */
    public static function updateCounter($name)
    {
        $value = self::get($name);
        $value = intval($value);
        if ($value) {
            self::set($name, $value+1);
        }
    }


    /**
     * Set Setting Value
     * @param $name
     * @param $value
     * @return mixed
     */
    public static function set($name, $value)
    {
        $model = Setting::where('key', $name)->first();
        if ($model) {
            $model->value = $value;
            $model->save();
        } else {
            // @todo handle error here ?
        }
    }


    /**
     * @param $name
     * @return mixed
     */
    public static function checkIntegration($name, $team_id = null)
    {
        $class = 'App\\Integration\\' . ucfirst($name);
        $integration = new $class;
        return $integration->get($name, $team_id);
    }


    public static function getAutoRenewHandler($name, $team_id = null){


    }


}
