<?php

use App\Team;
use App\User;
use Carbon\Carbon;
use App\Integration;
use App\Reservation;
use App\Transaction;
use App\UnitCleaning;
use App\IptvGuestNeed;
use GuzzleHttp\Client;
use App\IntegrationLog;
use App\PmsNotification;
use App\UnitMaintenance;
use App\GroupReservation;
use App\Services\ContractService;
use Aghanem\Jawaly\Facades\Jawaly;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Liliom\Unifonic\UnifonicFacade;
use Vinkla\Hashids\Facades\Hashids;
use App\Services\ZATCA\Phase1\Tags\Seller;
use GuzzleHttp\Exception\RequestException;
use App\Services\ZATCA\Phase1\GenerateQrCode;
use App\Services\ZATCA\Phase1\Tags\TaxNumber;
use App\Services\ZATCA\Phase1\Tags\InvoiceDate;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Services\ZATCA\Phase1\Tags\InvoiceTaxAmount;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Services\ZATCA\Phase1\Tags\InvoiceTotalAmount;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @author Emad Rashad
 * @description Helper Function for money format support in unsupported operating systems
 */
if (!function_exists('money_format')) {

    /**
     * Money Format Helper function
     * this helper function fixs the problem of call to undefind function on Windows Platform
     * it's a community bug , so we can create it here in our help , and we can hoot it into php.ini directly
     * @param string $format
     * @param float  $number
     * @return void
     */
    function money_format($format, $number)
    {
        $regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?' .
            '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
        if (setlocale(LC_MONETARY, 0) == 'C') {
            setlocale(LC_MONETARY, '');
        }
        $locale = localeconv();
        preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
        foreach ($matches as $fmatch) {
            $value = floatval($number);
            $flags = array(
                'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ?
                    $match[1] : ' ',
                'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
                'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                    $match[0] : '+',
                'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
                'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
            );
            $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
            $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
            $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits'];
            $conversion = $fmatch[5];

            $positive = true;
            if ($value < 0) {
                $positive = false;
                $value  *= -1;
            }
            $letter = $positive ? 'p' : 'n';

            $prefix = $suffix = $cprefix = $csuffix = $signal = '';

            $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
            switch (true) {
                case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                    $prefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                    $suffix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                    $cprefix = $signal;
                    break;
                case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                    $csuffix = $signal;
                    break;
                case $flags['usesignal'] == '(':
                case $locale["{$letter}_sign_posn"] == 0:
                    $prefix = '(';
                    $suffix = ')';
                    break;
            }
            if (!$flags['nosimbol']) {
                $currency = $cprefix .
                    ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                    $csuffix;
            } else {
                $currency = '';
            }
            $space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';

            $value = number_format(
                $value,
                $right,
                $locale['mon_decimal_point'],
                $flags['nogroup'] ? '' : $locale['mon_thousands_sep']
            );
            $value = @explode($locale['mon_decimal_point'], $value);

            $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
            if ($left > 0 && $left > $n) {
                $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
            }
            $value = implode($locale['mon_decimal_point'], $value);
            if ($locale["{$letter}_cs_precedes"]) {
                $value = $prefix . $currency . $space . $value . $suffix;
            } else {
                $value = $prefix . $value . $space . $currency . $suffix;
            }
            if ($width > 0) {
                $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                    STR_PAD_RIGHT : STR_PAD_LEFT);
            }

            $format = str_replace($fmatch[0], $value, $format);
        }
        return $format;
    }
}

/**
 * @author Emad Rashad
 * @description Helper to convert date into arabic date format
 */
if (!function_exists('arabic_date')) {
    function arabic_date($date)
    {
        //    $months = array("Jan" => "يناير", "Feb" => "فبراير", "Mar" => "مارس", "Apr" => "أبريل", "May" => "مايو", "Jun" => "يونيو", "Jul" => "يوليو", "Aug" => "أغسطس", "Sep" => "سبتمبر", "Oct" => "أكتوبر", "Nov" => "نوفمبر", "Dec" => "ديسمبر");
        $months = array("Jan" => "١", "Feb" => "٢", "Mar" => "٣", "Apr" => "٤", "May" => "٥", "Jun" => "٦", "Jul" => "٧", "Aug" => "٨", "Sep" => "٩", "Oct" => "١٠", "Nov" => "١١", "Dec" => "١٢");

        $en_month = date("M", strtotime($date));

        $arabic_month = $months[$en_month];

        //  in case we need arabic months
        //    foreach ($months as $en => $ar) {
        //        if ($en == $en_month) { $ar_month = $ar; }
        //    }

        $find = array("Sat", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri");
        $replace = array("السبت", "الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة");
        $ar_day_format = date('D'); // The Current Day
        $ar_day = str_replace($find, $replace, $ar_day_format);

        header('Content-Type: text/html; charset=utf-8');
        $standard = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $eastern_arabic_symbols = array("٠", "١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩");
        // this line if we wanna add the day in arabic
        //  $current_date = $ar_day.' '.date('d').' / '.$ar_month.' / '.date('Y');
        $current_date = date('j', strtotime($date)) . '-' . $arabic_month . '-' . date('Y');

        $arabic_date = str_replace($standard, $eastern_arabic_symbols, $current_date);

        return $arabic_date;
    }
}

if (!function_exists('payment_type_translation')) {
    function payment_type_translation($label)
    {

        return __(ucfirst($label));
    }
}
if (!function_exists('getCurrency')) {


    function getCurrency()
    {
        if(auth()->user()) {

            return auth()->user()->currentTeam->currency;
        }
    }

}

if (!function_exists('getVatPercentageForUnit')) {
    function getVatPercentageForUnit($team_id)
    {
        return DB::table('settings')->where('key', '=', 'tax')->where('team_id', '=', $team_id)->value('value');
    }
}

if (!function_exists('getEwaPercentageForUnit')) {
    function getEwaPercentageForUnit($team_id)
    {
        return DB::table('settings')->where('key', '=', 'accommodation_tax')->where('team_id', '=', $team_id)->value('value');
    }
}

if (!function_exists('getTourismPercentageForUnit')) {
    function getTourismPercentageForUnit($team_id)
    {
        return DB::table('settings')->where('key', '=', 'tourism_tax')->where('team_id', '=', $team_id)->value('value');
    }
}

if (!function_exists('getSettingItem')) {
    function getSettingItem($team_id, $key)
    {
        return DB::table('settings')->where('key', '=', $key)->where('team_id', '=', $team_id)->value('value');
    }
}

if (!function_exists('getVatTotalForUnit')) {
    function getVatTotalForUnit($total_price, $ewaTotal, $vatPercentage, $formatted = false)
    {

        $vatToPay = ((floatval($total_price) + floatval($ewaTotal)) / 100) * floatval($vatPercentage);
        return $formatted ? number_format($vatToPay, 2) : $vatToPay;
    }
}

if (!function_exists('getEwaTotalForUnit')) {
    function getEwaTotalForUnit($total_price, $ewaPercentage, $formatted = true)
    {

        $ewaPercentage = $ewaPercentage == "" ? 0 : $ewaPercentage;
        $ewaToPay = ($total_price / 100) * $ewaPercentage;
        return $formatted ? number_format($ewaToPay, 2) : $ewaToPay;
    }
}

if (!function_exists('getTtxTotalForUnit')) {
    function getTtxTotalForUnit($total_price, $tourismPercentage, $formatted = true)
    {
        $ttxToPay = ($total_price / 100) * $tourismPercentage;
        return $formatted ? number_format($ttxToPay, 2) : $ttxToPay;
    }
}


if (!function_exists('sumSubTotalWithServices')) {
    function sumSubTotalWithServices($reservation, $formatted = true)
    {
        $services_sub_total = 0;
        if ($reservation->services->count()) {
            foreach ($reservation->services as $transaction) {
                $services_sub_total += $transaction->meta['sub_total'];
            }
        }
        $sub_total = $reservation->sub_total + $services_sub_total;
        return $formatted ? number_format($sub_total, 2) : $sub_total;
    }
}

if (!function_exists('taxesCalculator')) {
    function taxesCalculator($reservation, $type, $formatted = true)
    {
        $service_tax_total = 0;
        if ($reservation->services->count()) {
            foreach ($reservation->services as $transaction) {
                $service_tax_total += $transaction->meta[$type . '_total'];
            }
        }
        $reservation_tax = $type == 'vat' ? $reservation->vat_total : $reservation->ttx_total;

        $total_tax = $reservation_tax + $service_tax_total;
        return $formatted ? number_format($total_tax, 2) : $total_tax;
    }
}

if (!function_exists('taxesTotals')) {
    function taxesTotals($reservation)
    {
        $ewa_percentage = number_format($reservation->ewa_total /  ($reservation->sub_total / 100), 2);
        $vat_percentage = number_format($reservation->vat_total /  (($reservation->sub_total + $reservation->ewa_total)  / 100), 2);
        $ttx_percentage = number_format($reservation->ttx_total /  ($reservation->sub_total / 100), 2);
        return ['ewa_percentage' => $ewa_percentage, 'vat_percentage' => $vat_percentage, 'ttx_percentage' => $ttx_percentage];
    }
}

if (!function_exists('getRelationships')) {
    function getRelationships($class)
    {
        $reflector = new ReflectionClass($class);

        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType) {
                if (in_array($returnType->getName(), [HasOne::class, HasMany::class, BelongsTo::class, BelongsToMany::class])) {
                    $relations[] = $reflectionMethod;
                }
            }
        }

        return $relations;
    }
}





if (!function_exists('checkAtLeastServiceTransactionHasTtx')) {
    function checkAtLeastServiceTransactionHasTtx($services)
    {


        $ttxHolder = array();
        if (count($services)) {

            foreach ($services as $transaction) {
                if ($transaction->meta['ttx_total'] != 0) {
                    $ttxHolder[] = $transaction->meta['ttx_total'];
                }
            }

            if (count($ttxHolder) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}


if (!function_exists('serviceTransactionHasTtxEnabled')) {
    function serviceTransactionHasTtxEnabled($services)
    {

        $ttx_holder = array();
        foreach ($services as $service) {
            if ($service['ttxIsChecked'] != 0) {
                $ttx_holder[] = $service['ttx'];
            }
        }

        if (count($ttx_holder)) {
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('serviceTransactionHasVatEnabled')) {
    function serviceTransactionHasVatEnabled($services)
    {

        $vat_holder = array();
        foreach ($services as $service) {

            if ($service['vatIsChecked']) {
                $vat_holder[] = $service['vat'];
            }
        }

        if (count($vat_holder)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('convertGregorianToHijriDate')) {
    function convertGregorianToHijriDate($date)
    {

        $year = \Carbon\Carbon::parse($date)->format('Y');
        $month = \Carbon\Carbon::parse($date)->format('m');
        $day = \Carbon\Carbon::parse($date)->format('d');


        $hijri = new \App\Services\Hijri_GregorianConvert();
        $hijri = $hijri->g2u($day, $month, $year);

        // return  $hijri['year'] . '/' . $hijri['month'] . '/' .  $hijri['day'];
        return  $hijri['day'] . '/' . $hijri['month'] . '/' . $hijri['year'] ;
    }
}

if (!function_exists('convertGregorianToHijriDateFormatedWithHijriMonth')) {
    function convertGregorianToHijriDateFormatedWithHijriMonth($date)
    {

        $year = \Carbon\Carbon::parse($date)->format('Y');
        $month = \Carbon\Carbon::parse($date)->format('m');
        $day = \Carbon\Carbon::parse($date)->format('d');


        $hijri = new \App\Services\Hijri_GregorianConvert();
        $hijri->setLang(app()->getLocale());
        $hijiri_date = $hijri->date('j F, Y',strtotime($date),1);
        return $hijiri_date;
    }
}

if (!function_exists('getDayNameFromDate')) {
    function getDayNameFromDate($date)
    {
        return \Carbon\Carbon::parse($date)->getTranslatedDayName();
    }
}


if (!function_exists('getNotificationControlKeyValue')) {
    function getNotificationControlKeyValue($key, $team_id)
    {
        return json_decode(\App\NotificationControl::where('key', $key)->where('team_id', $team_id)->first());
    }
}


if (!function_exists('getCauserUser')) {
    function getCauserUser($id)
    {
        return \App\User::find($id);
    }
}


if (!function_exists('setDecimalPlaces')) {
    function setDecimalPlaces($places)
    {
        \Illuminate\Support\Facades\Config::set('places', $places);
    }
}

if (!function_exists('setSlackWebHook')) {
    function setSlackWebHook($hook)
    {
        if (app()->environment() === 'production') {
            \Illuminate\Support\Facades\Config::set('logging.channels.slack.url', $hook);
        }
    }
}

if (!function_exists('checkIfUnitHasReservation')) {
    function checkIfUnitHasReservation($unit_id, $start)
    {
        return (bool) DB::table('reservations as r')
            ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
            ->leftJoin('highlights as h', 'c.highlight_id', '=', 'h.id')
            ->select(
                'r.id as rid',
                'r.checked_in as rchi',
                'r.date_in as rdi',
                'r.date_out as rdo',
                'r.status',
                'c.name as cname',
                'h.name as hlabel',
                'h.color as hcolor'
            )
            ->whereRaw('? between r.date_in and r.date_out', [Carbon::parse($start)->format('Y-m-d')])
            ->where('r.date_out', '!=', Carbon::parse($start)->format('Y-m-d'))
            ->whereNull('r.checked_out')
            ->whereNull('r.deleted_at')
            ->whereNotIn('r.status', ['timeout', 'canceled'])
            ->where('r.unit_id', $unit_id)
            ->get()
            ->count();
    }
}

if (!function_exists('checkIfUnitHasReservationAiosell')) {
    function checkIfUnitHasReservationAiosell($unit_id, $start)
    {
        return (bool) DB::table('reservations as r')
            ->leftJoin('customer as c', 'r.customer_id', '=', 'c.id')
            ->leftJoin('highlights as h', 'c.highlight_id', '=', 'h.id')
            ->select(
                'r.id as rid',
                'r.checked_in as rchi',
                'r.date_in as rdi',
                'r.date_out as rdo',
                'r.status',
                'c.name as cname',
                'h.name as hlabel',
                'h.color as hcolor'
            )
            ->whereRaw('? between r.date_in and r.date_out', [Carbon::parse($start)->format('Y-m-d')])
            ->where('r.date_out', '!=', Carbon::parse($start)->format('Y-m-d'))
            ->whereNull('r.checked_out')
            ->whereNull('r.cmBookingId')
            ->whereNull('r.deleted_at')
            ->whereNotIn('r.status', ['timeout', 'canceled'])
            ->where('r.unit_id', $unit_id)
            ->get()
            ->count();
    }
}

if (!function_exists('jawalySendSms')) {
    function jawalySendSms($api_key, $api_secret, $sender, $message, $mobileNumber)
    {

        $curl = curl_init();
        $app_id = $api_key;
        $app_sec = $api_secret;
        $app_hash  = base64_encode("$app_id:$app_sec");
        $messages = [];
        $messages["messages"] = [];
        $messages["messages"][0]["text"] = $message;
        $messages["messages"][0]["numbers"][] = $mobileNumber;
        $messages["messages"][0]["sender"] = $sender;

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-sms.4jawaly.com/api/v1/account/area/sms/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($messages),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $app_hash
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
        // $user = $username;
        // $password = $password;
        // $sendername = urlencode($senderName);
        // $text = urlencode($message);
        // $to = $mobileNumber;

        // $url = "http://www.4jawaly.net/api/sendsms.php?username=$user&password=$password&numbers=$to&message=$text&sender=$sendername&unicode=E&return=json";

        // $res = file_get_contents($url);
        // return json_decode($res, true);
    }
}

if (!function_exists('unifonicSendSms')) {
    function unifonicSendSms($appSid, $recipient, $message)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'http://basic.unifonic.com'
        ]);
        $response = $client->post('/rest/SMS/messages', [
            'form_params' => [
                'AppSid' => $appSid,
                'Recipient' => $recipient,
                'Body' => $message,
                'ResponseType' => 'JSON'
            ]
        ]);
        return  json_decode($response->getBody(), true);
    }
}

if (!function_exists('sendFsms')) {
    function sendFsms($appSid, $recipient, $message)
    {
        $url = env('FSMS_URL').'api/send-sms';
        $client = new Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'Api_key' => $appSid,
                'mobile_number' => $recipient,
                'message' => $message
            ]
        ]);

        return  json_decode($response->getBody(), true);
    }
}
if (!function_exists('sendSms')) {
    /**
     * Send SMS  Helper function
     * @param int $team_id
     * @param string  $message
     * @return void
     */

    function sendSms($team_id, $message, $phone, $default = false, $yallabnb = false)
    {
        //fsms
        if($integration = Integration::findByKeyAndTeamId('fsms', $team_id)->first()) {
            $credentials = json_decode($integration->values, true);
            // dd($credentials);
            if (isset($credentials['appSid']) and !is_null($credentials['appSid'])) {
         // call sendFsms function
         $response = sendFsms($credentials['appSid'], $phone, $message);

            }
        }
        elseif ($integration = Integration::findByKeyAndTeamId('Jawaly', $team_id)->first()) {
            $credentials = json_decode($integration->values, true);
            // config()->set('jawaly.username', $credentials['username']);
            // config()->set('jawaly.password', $credentials['password']);
            // config()->set('jawaly.sender', $credentials['sender']);
            if(isset($credentials['api_key']) && isset($credentials['api_secret']) && isset($credentials['sender'])) {
                $smsResponse = jawalySendSms($credentials['api_key'], $credentials['api_secret'], $credentials['sender'], $message, $phone);

                // if(config('jawaly.username') == $credentials['username']){
                $gateMessage = isset($smsResponse['message']) ? $smsResponse['message'] : '';

                if ($smsResponse['code'] == 200) {
                    // $send = Jawaly::send($message, $phone);
                    $log = new IntegrationLog();
                    $log->team_id = $team_id;
                    $log->type = 'Jawaly';
                    $log->response = $smsResponse;
                    $log->action = 1;
                    $log->status = $smsResponse['code']  == 200 ? 1 : 2;
                } else {
                    $log = new IntegrationLog();
                    $log->team_id = $team_id;
                    $log->type = 'Jawaly';
                    $log->response = $smsResponse;
                    $log->action = 2;
                    $log->status =  2;
                }
                $log->payload = [
                    'message' => $message,
                    'phone' => $phone,
                    'response' => $smsResponse,
                    'credentials' => $credentials,
                    'serverMsg' => $gateMessage
                ];

                $log->save();
            }
        } else {
            if ($default) {
                if ($yallabnb) {
                    \Illuminate\Support\Facades\Config::set('services.unifonic.app_id', config('app.yallabnb_unifonic'));
                } else {
                    \Illuminate\Support\Facades\Config::set('services.unifonic.app_id', config('app.fandaqah_unifonic'));
                }

                $credentials = [
                    "type" =>  "sms",
                    "appSid" =>  config('app.fandaqah_unifonic')
                ];

                /**
                 * @see: this is the part of Unifonic our default sms provider we comment it for now
                 */
                $smsResponse = unifonicSendSms(config('app.fandaqah_unifonic'), $phone, $message);

                $log = new IntegrationLog();
                $log->team_id = $team_id;
                $log->type = 'unifonic';
                $log->response = $smsResponse;
                $log->action = 1;
                $log->payload = [
                    'message' => $message,
                    'phone' => $phone,
                    'credentials' => $credentials
                ];
                $log->status = $smsResponse['success'] ? 1 : 2;
                $log->save();
                return $log;
                /**
                 * @see : This hack just for now we use Jawaly SMS Provider as the default provider
                 * because Unifonic is not stable and they have a very good customer service manipulators
                 */


                /**
                 * @note : disable 4Jawaly
                 */
                /*
                 $credentials = [
                    'username' => 'fondoky',
                    'password' => '0561187386',
                    'sender' => 'FANDAQAH'
                ];
                $smsResponse = jawalySendSms($credentials['username'],$credentials['password'],$credentials['sender'],$message,$phone);

                // if(config('jawaly.username') == $credentials['username']){
                if($smsResponse['Code'] == 100){
                    // $send = Jawaly::send($message, $phone);
                    $gateMessage = isset($smsResponse['MessageIs']) ? $smsResponse['MessageIs'] : '';
                    $log = new IntegrationLog();
                    $log->team_id = $team_id;
                    $log->type = 'Jawaly';
                    $log->response = $smsResponse;
                    $log->action = 1;
                    $log->payload = [
                        'message' => $message,
                        'phone' => $phone,
                        'response' => $smsResponse,
                        'credentials' => $credentials,
                        'serverMsg' => $gateMessage
                    ];
                    $log->status = $smsResponse['Code']  == 100 ? 1 : 2;
                }else{
                    $log = new IntegrationLog();
                    $log->response = 'Internal problem in config';
                    $log->status =  2;
                }
                $log->save();
                */
            } else {
                $log = new IntegrationLog();
                $log->team_id = $team_id;
                $log->type = 'sms gateway';
                $log->response = "don't conected with any sms gateway";
                $log->action = 1;
                $log->payload = [
                    'message' => $message,
                    'phone' => $phone
                ];
                $log->status = 2;
                $log->save();
            }
        }
        // return $log;
    }
}

if (!function_exists('groupReservationHandler')) {
    function groupReservationHandler(GroupReservation $groupReservation)
    {
        $reservations = $groupReservation->reservations;
        $balances = [];
        foreach ($reservations as $reservation) {
            $balances[] = $reservation->balance / ($reservation->wallet->decimal_places == 3 ? 1000 : 100);
        }
        $groupReservation->balance = array_sum($balances);
        $groupReservation->save();
    }
}

/**
 * this function provide the domain of group reservation with needed data
 */
if (!function_exists('startAndEndDateCalculatorWithNights')) {
    function startAndEndDateCalculatorWithNights($reservations)
    {
        $start  = Carbon::parse($reservations->sortBy('date_in')->first()->date_in);
        $end    = Carbon::parse($reservations->sortByDesc('date_out')->first()->date_out);

        $firstCheckInObj  = $reservations->where('checked_out', '=', null)->where('checked_in', '!=', null)->sortBy('checked_in')->first();
        $lastCheckOutObj   = $reservations->where('checked_out', '!=', null)->sortByDesc('checked_out')->first();

        $nights = $end->diffInDays($start);
        return [
            'start_date' => $start->format('Y/m/d'),
            'end_date' => $end->format('Y/m/d'),
            'first_checked_in_date' => $firstCheckInObj ? Carbon::parse($firstCheckInObj->checked_in)->translatedFormat('Y/m/d h:i A') : null,
            'pure_first_checked_in_date' => $firstCheckInObj ? Carbon::parse($firstCheckInObj->checked_in)->format('Y-m-d H:i') : null,
            'last_checked_out_date' => $lastCheckOutObj ? $lastCheckOutObj->checked_out : null,
            'pure_last_checked_out_date' => $lastCheckOutObj ? Carbon::parse($lastCheckOutObj->checked_out)->format('Y-m-d H:i') : null,
            'nights' => $nights
        ];
    }
}

if (!function_exists('groupReservationsUnitCount')) {
    function groupReservationsUnitCount($reservation)
    {

        $main_reservation = null;
        if (is_null($reservation->attachable_id)) {
            $main_reservation = $reservation;
            $push_main_reservation_to_collection = false;
        } else {
            $main_reservation = Reservation::find($reservation->attachable_id);
            $push_main_reservation_to_collection = true;
        }

        $reservations = Reservation::with('wallet', 'unit')
            ->where('reservation_type', 'group')
            ->where('company_id', $reservation->company_id)
            ->where(function ($query) use ($reservation, $main_reservation) {
                return $query->with('unit')->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
            })
            ->whereIn('status', ['confirmed', 'canceled'])
            // ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->get();

        if ($push_main_reservation_to_collection) {
            $reservations->push($main_reservation);
        }

        return count($reservations);
    }
}


if (!function_exists('generateQrForInvoice')) {
    function generateQrForInvoice($total_price, $total_vat, $team, $date = null)
    {

        $displayQRCodeAsBase64 = GenerateQrCode::fromArray([
            new Seller($team->name), // seller name
            new TaxNumber(getSettingItem($team->id, 'tax_number')), // seller tax number
            new InvoiceDate(Carbon::parse($date)->toIso8601ZuluString()),
            new InvoiceTotalAmount(number_format($total_price, 2)), // invoice total amount
            new InvoiceTaxAmount(number_format($total_vat, 2)) // invoice tax amount
        ])->render();

        return $displayQRCodeAsBase64;
    }
}

if (!function_exists('shareableGroupBalance')) {
    function shareableGroupBalance($reservation)
    {
        if ($reservation->reservation_type == 'group') {
            $reservation['attachable_reservations_count'] = count($reservation->attachedReservations());
            $balances = [];
            $main_reservation = null;
            $push_main_reservation_to_collection = false;
            if (is_null($reservation->attachable_id)) {
                $main_reservation = $reservation;
                $push_main_reservation_to_collection = false;
            } else {
                $main_reservation = Reservation::find($reservation->attachable_id);
                $push_main_reservation_to_collection = true;
            }

            $reservations = Reservation::with('wallet')
                ->where('reservation_type', 'group')
                ->where('company_id', $reservation->company_id)
                ->where(function ($query) use ($reservation, $main_reservation) {
                    return $query->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
                })
                ->where('status', 'confirmed')
                ->whereNull('deleted_at')
                ->get();

            if ($push_main_reservation_to_collection) {
                $reservations->push($main_reservation);
            }
            foreach ($reservations as $reservationObject) {
                $balances[] = $reservationObject->wallet->decimal_places == 3 ? $reservationObject->balance / 1000 : $reservationObject->balance / 100;
            }
            return array_sum($balances);
        }
    }
}

if (!function_exists('getTheLowestDate')) {
    function getTheLowestDate($periods)
    {
        return Carbon::parse(min($periods))->format('Y/m/d');
    }
}

if (!function_exists('getTheHighestDate')) {
    function getTheHighestDate($periods)
    {
        return Carbon::parse(max($periods))->format('Y/m/d');
    }
}

if (!function_exists('getLastCleaning')) {
    function getLastCleaning($unit_id, $is_completed_at = false)
    {
        if(!$is_completed_at) {
            return UnitCleaning::where('unit_id', $unit_id)->whereNull('completed_at')->first();
        } else {
            return UnitCleaning::where('unit_id', $unit_id)->latest()->first();
        }
    }
}

if (!function_exists('getLastMaintenance')) {
    function getLastMaintenance($unit_id)
    {
        return UnitMaintenance::where('unit_id', $unit_id)->whereNull('completed_at')->first();
    }
}
if (!function_exists('createDefaultRolesForTeam')) {
    function createDefaultRolesForTeam($team_id)
    {
        $roles_engine = config('novaroleswithpermissions.roles');
        foreach ($roles_engine as $role_name => $permissions_engine) {
            $role = new Role();
            $role->name = $role_name;
            $role->slug = strtolower(str_replace(' ', '_', $role_name));
            $role->team_id = $team_id;
            $role->deletable = 1;
            $role->save();

            foreach ($permissions_engine['permissions'] as  $permission_slug) {
                Permission::create([
                    'role_id' => $role->id,
                    'permission_slug' => $permission_slug,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}

if(!function_exists('httpBuildQueryForCurl')) {
    function httpBuildQueryForCurl($arrays, &$new = array(), $prefix = null)
    {

        if (is_object($arrays)) {
            $arrays = get_object_vars($arrays);
        }

        foreach ($arrays as $key => $value) {
            $k = isset($prefix) ? $prefix . '[' . $key . ']' : $key;
            if (is_array($value) or is_object($value)) {
                httpBuildQueryForCurl($value, $new, $k);
            } else {
                $new[$k] = $value;
            }
        }
    }
}

if (!function_exists('guzzleRequester')) {
    function guzzleRequester($url, $headers, $method = 'POST', $data = [])
    {

        try {
            //code...
            $client = new Client([
                'headers' => $headers,
                 // Wait 60 seconds for the server to send a response
                 'timeout' => 60,
                 // Wait 10 second to connect to the server
                 'connect_timeout' => 10,
                 'exceptions' => true
            ]);

            $initiateRequest = $client->request($method, $url, [
                $method == 'GET' ? 'query' : 'body' => \GuzzleHttp\json_encode($data),
            ]);
            return json_decode($initiateRequest->getBody()->getContents());
        } catch (\Throwable $th) {
            return "There was a problem with your request: " . $th->getMessage();
        }
    }
}



if (!function_exists('getIptvRequests')) {
    function getIptvRequests($team_id)
    {
        return IptvGuestNeed::with('reservation')->where('team_id',$team_id)->whereNull('treated_by')->where('is_treated',0)->orderByDesc('created_at')->get();
    }
}

if (!function_exists('totalEscortsInGroupReservation')) {
    function totalEscortsInGroupReservation($reservation)
    {

        $main_reservation = null;
        if (is_null($reservation->attachable_id)) {
            $main_reservation = $reservation;
            $push_main_reservation_to_collection = false;
        } else {
            $main_reservation = Reservation::find($reservation->attachable_id);
            $push_main_reservation_to_collection = true;
        }


        $reservations = Reservation::with('reservation_guests')
            ->where('reservation_type', 'group')
            ->where('company_id', $reservation->company_id)
            ->where(function ($query) use ($reservation, $main_reservation) {
                return $query->where('id', $reservation->id)->orWhere('attachable_id', $main_reservation->id);
            })
            ->where('status', 'confirmed')
            // ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->orderBy('created_at')
            ->get();

        $total_escorts = 0;

        if(count($reservations)){
            foreach($reservations as $obj){
                $total_escorts += $obj->reservation_guests()->count();
            }
        }

        return $total_escorts;
    }
}

    if (!function_exists('startAndEndDateCalculatorWithNightsForGroupContract')) {
        function startAndEndDateCalculatorWithNightsForGroupContract($reservations)
        {
            $start  = Carbon::parse($reservations->sortBy('date_in')->first()->date_in);
            $end    = Carbon::parse($reservations->sortByDesc('date_out')->first()->date_out);

            $firstCheckInObj  = $reservations->where('checked_in', '!=', null)->sortBy('checked_in')->first();
            $lastCheckOutObj   = $reservations->where('checked_out', '!=', null)->sortByDesc('checked_out')->first();

            $nights = $end->diffInDays($start);
            return [
                'start_date' => $start->format('Y/m/d'),
                'end_date' => $end->format('Y/m/d'),
                'first_checked_in_date' => $firstCheckInObj ? Carbon::parse($firstCheckInObj->checked_in)->translatedFormat('Y/m/d h:i A') : null,
                'pure_first_checked_in_date' => $firstCheckInObj ? Carbon::parse($firstCheckInObj->checked_in)->format('Y-m-d H:i') : null,
                'last_checked_out_date' => $lastCheckOutObj ? $lastCheckOutObj->checked_out : null,
                'pure_last_checked_out_date' => $lastCheckOutObj ? Carbon::parse($lastCheckOutObj->checked_out)->format('Y-m-d H:i') : null,
                'nights' => $nights
            ];
        }
    }



    if (!function_exists('hashidEncoderAndDecoder')) {
        function hashidEncoderAndDecoder($id,$default = 'encode')
        {
            if($default == 'decode') {
                return isset(Hashids::decode($id)[0]) ? Hashids::decode($id)[0] : null;
            }
            return Hashids::encode($id);
        }
    }

    if (!function_exists('unreadPmsNotifications')) {
        function unreadPmsNotifications()
        {
            if(auth()->check()) {
                return count(PmsNotification::where('team_id',auth()->user()->current_team_id)->whereNull('read_at')->get());
            }
            return 0;
        }
    }

    if (!function_exists('getShortUrl')) {
        function getShortUrl($url)
        {
            try {
                $client = new Client();

                $response = $client->post(url('/shorten'), [
                    'json' => [
                        'destination_url' => $url,
                    ],
                    'timeout' => 5, // Optional: Set timeout to avoid hanging
                ]);

                $data = json_decode($response->getBody(), true);

                if (isset($data['short_url'])) {
                    return $data['short_url'];
                }

                // Handle unexpected response structure
                Log::warning('Shorten URL response missing short_url key', ['response' => $data]);
                return $url; // fallback to original
            } catch (RequestException $e) {
                Log::error('Short URL request failed', [
                    'message' => $e->getMessage(),
                    'url' => $url
                ]);
                return $url; // fallback to original
            } catch (\Exception $e) {
                Log::error('Unexpected error during short URL generation', [
                    'message' => $e->getMessage(),
                    'url' => $url
                ]);
                return $url; // fallback to original
            }
        }
    }
    if (!function_exists('sendMailUsingMailMicroservice')) {
        function sendMailUsingMailMicroservice($data)
        {
            $params = [
                'email' => $data['to'],
                'replyTo' => $data['reply_to'],
                'subject' => $data['subject'],
                'template' => $data['html'],
            ];
            $url = env('MS_MAIL_URL').'/api/send-mail';
            $curl = curl_init();
            $username = env('MS_MAIL_FANDAQAH_USER');
            $password = env('MS_MAIL_FANDAQAH_PWD');
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic ' . base64_encode($username . ':' . $password),
                ),
            ));

            $response = curl_exec($curl);
            $response = json_decode($response, true);
            curl_close($curl);
        }
    }

    if (!function_exists('contractSnapshotGenerator')) {
        function contractSnapshotGenerator($reservation, $status = 'draft',$code = null)
        {
            try {
                $isEnabledDigitalSignature = Team::where('id', $reservation->team_id)
                    ->value('enable_digital_signature');
    
                if ($isEnabledDigitalSignature) {
                    $contractService = new \App\Services\ContractService();
                    $contract = $contractService->makeContractSnapshot($reservation, $status , $code);
    
                    return $contract;
                }
    
                return response()->json([
                    'success' => false,
                    'message' => 'Digital signature is not enabled for this team.',
                ]);
    
            } catch (\Exception $e) {
                Log::error('Failed to generate contract snapshot for reservation ID: ' . $reservation->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
    
                return response()->json([
                    'success' => false,
                    'exception' => $e->getMessage(),
                ], 500);
            }
        }
    }

    if (!function_exists('checkPermissionForUserInMultipleRolesForTeam')) {
        function checkPermissionForUserInMultipleRolesForTeam($user_id, $team_id, $permissions)
        {
            try {
                $roles = DB::table('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->join('role_permission', 'role_permission.role_id', '=', 'roles.id')
                    ->where('role_user.user_id', $user_id)
                    ->where('roles.team_id', $team_id)
                    ->whereIn('role_permission.permission_slug', $permissions)
                    ->pluck('permission_slug')
                    ->unique()
                    ->values();
    
                $result = [];
                foreach ($permissions as $slug) {
                    $result[$slug] = $roles->contains($slug);
                }
    
                return $result;
    
            } catch (\Throwable $e) {
                Log::error('DB permission check failed', [
                    'user_id' => $user_id,
                    'team_id' => $team_id,
                    'error' => $e->getMessage(),
                ]);
    
                return [];
            }
        }
    }
    
    
    
