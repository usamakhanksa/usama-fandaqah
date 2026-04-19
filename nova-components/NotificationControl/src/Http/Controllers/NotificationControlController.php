<?php

namespace SureLab\NotificationControl\Http\Controllers;

use App\Team;
use App\Handlers\Settings;
use App\NotificationControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\DefaultNotificationControlSettings;

class NotificationControlController extends Controller
{
    public function getNotificationSettings()
    {
        $currentTeam = Team::find(auth()->user()->current_team_id);
        $settings = NotificationControl::where('team_id', auth()->user()->current_team_id)->get();

        $notificationQueryObj = NotificationControl::query();
        $alert_payment_successful_obj = $notificationQueryObj->whereRaw(' `key` = ? ', ['alert_payment_successful'])->whereRaw(' team_id = ? ', [auth()->user()->current_team_id])->first();
        if (is_null($alert_payment_successful_obj)) {
            $contentOptions = new \stdClass();
            $contentOptions->contractNumber = false;
            $contentOptions->date = false;
            $contentOptions->unitName = false;
            $contentOptions->invoiceAmount = false;

            $data = new \stdClass();
            $data->email = false;
            $data->sms = false;
            $data->content = __('Payment has been made successfully , Enjoy your stay at :hotel_name', ['hotel_name' => auth()->user()->currentTeam->name]);
            $data->contentOptions = $contentOptions ;

            DB::table('notification_controls')->insert(
                [
                    ['team_id' => auth()->user()->current_team_id, 'key' => 'alert_payment_successful', 'value' => json_encode($data), 'type' => 'customer'],
                ]
            );
        }
        if (!count($settings)) {
            // Make use of the job  , flush default settings
            $defaultNotificationControlSettings = new DefaultNotificationControlSettings($currentTeam);
            $defaultNotificationControlSettings->handle();
            return response()->json(NotificationControl::where('team_id', auth()->user()->current_team_id)->get());
        }

        return response()->json($settings);
    }

    public function updateNotificationControlSettings(Request $request)
    {
        $notificationControlSettings = json_decode($request->get('data'));
        $settings = NotificationControl::where('team_id', auth()->user()->current_team_id)->get();

        if ($settings) {
            $this->updateNotificationSettings($settings, $notificationControlSettings);
            return response()->json(['status' => 'updated']);
        }
    }

    protected function updateNotificationSettings($settings, $notificationControlSettings)
    {
        foreach ($settings as $setting) {
            switch ($setting->key) {
                case 'alert_reservation_added':
                    $setting->value = $notificationControlSettings->alert_reservation_added->value;
                    $setting->save();
                    break;
                case 'alert_reservation_deleted':
                    $setting->value = $notificationControlSettings->alert_reservation_deleted->value;
                    $setting->save();
                    break;
                case 'alert_reservation_canceled':
                    $setting->value = $notificationControlSettings->alert_reservation_canceled->value;
                    $setting->save();
                    break;
                case 'alert_daily_report':
                    $setting->value = $notificationControlSettings->alert_daily_report->value;
                    $setting->save();
                    break;
                case 'alert_email':
                    $setting->value = $notificationControlSettings->alert_email->value;
                    $setting->save();
                    break;
                case 'alert_phone':
                    $setting->value = $notificationControlSettings->alert_phone->value;
                    $setting->save();
                    break;
                case 'alert_reservation_checked_in':
                    $setting->value = $notificationControlSettings->alert_reservation_checked_in->value;
                    $setting->save();
                    break;
                case 'alert_reservation_checked_out':
                    $setting->value = $notificationControlSettings->alert_reservation_checked_out->value;
                    $setting->save();
                    break;
                case 'alert_payment_successful':
                    $setting->value = $notificationControlSettings->alert_payment_successful->value;
                    $setting->save();
                    break;
                default:
                    break;
            }
        }
    }
}
