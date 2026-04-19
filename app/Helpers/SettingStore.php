<?php

namespace App\Helpers;

class SettingStore {
    static public function getUserSetting($value) {
        return auth()->user()->teams->where('id', auth()->user()->current_team_id)->first()[$value];
    }
}