<?php

namespace Surelab\Settings\Http\Controllers;

use App\Team;
use App\User;
use App\Setting;
use Carbon\Carbon;
use App\ActionType;
use App\Integration;
use App\TeamCounter;
use GuzzleHttp\Client;
use App\ReservationService;
use Illuminate\Support\Str;
use App\IntegrationSettings;
use Illuminate\Http\Request;
use App\PhoneVerificationCode;
use Illuminate\Validation\Rule;
use App\Events\IndexUnitHandler;
use App\ReservationServiceMapper;
//Client
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Liliom\Unifonic\UnifonicFacade;
use App\Mail\ScthTenantDisconnected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ProfileRequest;
use Surelab\Settings\Traits\Settings;
use Illuminate\Support\Facades\Schema;
use App\Services\ZATCA\Phase2\VerifyOtp;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;
use Surelab\Settings\ValueObjects\SettingRegister;
use App\Http\Requests\StoreReservationServiceRequest;
use App\Http\Requests\UpdateReservationServiceRequest;

/**
 * Class SettingsController
 * @package Surelab\Settings\Http\Controllers
 */
final class SettingsController
{
    /**
     * Get settings.
     * @return JsonResponse
     */
    public function get($group): JsonResponse
    {
        return response()->json(SettingRegister::getGroupByName($group));
    }

    /**
     * Return Integration setting by key
     * @param $key
     * @return JsonResponse
     */
    public function getIntegrations($key)
    {
        $list = IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first();

        if ($key == 'fsms' ) {
            // call getFsmsBalance
            $integration = Integration::where('key', 'fsms')
            ->where('team_id', auth()->user()->current_team_id)
            ->first();

            if ($integration) {
                $integration = json_decode($integration->values, true);
                $client = new Client();
                $response = $client->request('POST', env('FSMS_URL') . 'api/get-balance', [
                    'form_params' => [
                        'Api_key' => $integration['appSid']
                    ]
                ]);
                $body = json_decode($response->getBody()->getContents(), true);
                // dd($body);
                if ($body['status'] == "success") {
                    $list->balance = $body['balance'];
                }
            }

        }
        return response()->json($list);
    }


    public function checkConnection($key)
    {
        $integration = Integration::where('key', $key)
            ->where('team_id', auth()->user()->current_team_id)
            ->first();

        if ($integration) {
            $integration = json_decode($integration->values, true);
            if ($key == 'fsms') {
             $check = new \App\Integration\Fsms();
                return response()->json($check->check($integration));
                if ($check->check($integration)) {
                    return response()->json(['status' => true]);
                }else{
                    return response()->json(['status' => false]);
                }

            }
        }
        return response()->json(['status' => false]);
    }
    //getFsmsBalance


    /**
     * Check if the database migrations are migrated.
     * @return JsonResponse
     */
    public function installed(): JsonResponse
    {
        return response()->json(['installed' => Schema::hasTable('settings')]);
    }

    /**
     * Update settings.
     * @param Request $request
     * @return JsonResponse
     */
    public function process(Request $request): JsonResponse
    {
        $values = $request->get('values');
        $group = $request->get('group');
        if (is_null($values)) {
            $values = [];
        }


        $settingRegister = SettingRegister::getInstance();

        $settingRegister->massUpdate($values);

        event(new IndexUnitHandler('update_settings', $values, auth()->user()->current_team_id));

        if($group == 'general'){
            $enableFreeze = Setting::where('key', 'enable_business_day_freeze')->value('value') ?? false;
            $businessDayHours = Setting::where('key', 'business_day_hours')->value('value');
            $businessDayHours = ($businessDayHours === null || $businessDayHours === '') ? 6 : $businessDayHours;
            if ($enableFreeze == 1) {
                //get now hour
                $now = Carbon::now();
                $hour = $now->hour;
                if($businessDayHours < $hour){
                    $startOfDay = now()->startOfDay();
                    $startOfDay = $startOfDay->subDay();
                    $freezeDate = $startOfDay->addHours($businessDayHours);
                    DB::table('settings')->updateOrInsert(
                        [
                            'key' => 'business_day_freeze_date',
                            'team_id' => Auth::user()->current_team->id,
                        ],
                        [
                            'value' => $freezeDate->toDateTimeString(),
                            'updated_at' => now(),
                        ]
                    );
                }
                $startOfDay = now()->startOfDay();
                $freezeDate = $startOfDay->addHours($businessDayHours);
                DB::table('settings')->updateOrInsert(
                    [
                        'key' => 'business_day_freeze_date',
                        'team_id' => Auth::user()->current_team->id,
                    ],
                    [
                        'value' => $freezeDate->toDateTimeString(),
                        'updated_at' => now(),
                    ]
                );
            }else{
                DB::table('settings')->where('key', 'business_day_freeze_date')->delete();
            }

    }
        return response()->json([
            'settings' => SettingRegister::getGroupByName($group),
            'message' => __('settings::settings.save_success')
        ]);
    }

    /**
     * Regester integration setting.
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        // TODO pass the new parameters for shomoos version 2
        $values = $request->get('values');
        $key = $request->get('key');
        $class = '\\App\\Integration\\' . ucfirst($key);
        //        class_exists($class);
        $integration = new $class();

        if ($integration->check($values) && $integration->save($values)) {
            activity()->performedOn((new IntegrationSettings()))->log(__('Integration with :KEY has been successfully done', ['key' => $key]));
            return response()->json([
                'settings' => IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first(),
                'message' => 'Fandaqah Integrated Successfully ',
                'success' => true
            ]);
        }

        return response()->json([
            'settings' => IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first(),
            'message' => 'Failed To connect! , please check your credentials',
            'errors' => $integration->errors(),
            'success' => false
        ]);
    }

    /**
     * Dissconnect from integration
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function disconnect(Request $request): JsonResponse
    {
        $key = $request->get('key');

        $integration = Integration::where(['key' => $key])->first();

        if ($integration && $integration->delete()) {
            activity()->performedOn((new IntegrationSettings()))->log(__('Integration with :KEY has been successfully removed', ['key' => $key]));
            if (app()->environment('production')) {
                // Mail::to(explode(',', env('SCTH_TENANT_DISCONNECTED_NOTIFIER_EMAILS')))->send(new ScthTenantDisconnected($integration));
            }
            return response()->json([
                'settings' => IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first(),
                'message' => 'Fandaqah disconnected from SCTH Successfully ',
                'success' => true
            ]);
        }

        return response()->json([
            '$key' => $key,
            '$integration' => $integration,
            'settings' => IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first(),
            'message' => 'Failed To disconnect!',
            'success' => false
        ]);
    }

    /**
     * Return Integration setting by key
     * @param $key
     * @return JsonResponse
     */
    public function getCounters()
    {
        if (TeamCounter::get()->count()) {
            $item = TeamCounter::get()->first();
        } else {
            $item = TeamCounter::create();
        }

        return response()->json($item);
    }

    /**
     * Return Integration setting by key
     * @param $key
     * @return JsonResponse
     */
    public function updateCounters(Request $request)
    {
        $counter = TeamCounter::where('team_id', auth()->user()->current_team_id)->first();
        $item = $request->get('column');
        $last_item = 'last_' . $request->get('column');

        $validatedData = $request->validate([
            $item => 'gte:' . (int)$counter->$last_item,
        ]);

        try {
            $counter->update([
                $item => $request->get($item),
            ]);
        } catch (\Exception $e) {
        }

        return response()->json($counter);
    }

    public function fetchSmsIntegration()
    {
        // $smsIntegrationsCount = Integration::where('team_id' , auth()->user()->current_team_id)->where('values->type' , 'sms')->whereNull('deleted_at')->count();
        $smsIntegrationsCount = Integration::where('team_id', auth()->user()->current_team_id)->whereIn('key', ['unifonic', 'Jawaly' ,'fsms'])->whereNull('deleted_at')->count();

        if (!$smsIntegrationsCount) {
            Setting::where('team_id', auth()->user()->current_team_id)->where('key', 'enable_ratings_sms')->update(['value' => 0]);
        }
        return response()->json($smsIntegrationsCount);
    }

    public function updateUserProfileData(Request $request)
    {
        $phoneNumber = str_replace(' ', '', $request['phone']);
        // Define validation rules and messages
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request['id']),
            ],
            'title' => 'required|string|max:255',
            'password' => 'nullable|min:8',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validator->errors()->all(), // Returns errors as an array
            ], 422);
        }
        $check_phone = DB::table('users')
        ->where("phone",$phoneNumber)
        ->where('id', '!=', $request['id'])
        ->exists();
        if($check_phone){
            return response()->json([
                'status' => false,
                'messages' => [__('messages.phone_number_already_exists')],
            ], 422);
        }
        //i want to remove any spacing in phone number



        // Update user data
        $user = User::findOrFail($request['id']);
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->phone = $phoneNumber;
        $user->title = $request['title'];
        if (!empty($request['password'])) {
            $user->password = bcrypt($request['password']);
        }

        $user->save();

        return response()->json([
            'status' => true,
            'user' => $user,
        ]);
    }

    public function sendEmailVerification(Request $request)
    {
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
        if (Str::endsWith($user->email, '@hotmail.com')) {
            $user->email_verified_at = Carbon::now(); // Auto-verify the email
            $user->save();

            return response()->json(['status' => true,'autoVerified'=>true,'message' => trans('messages.email_auto_verified')]);
        }
        $verificationHash = hash('sha256', $user->email);
        $verificationUrl = url('/email/verify/' . $user->id . '/' . $verificationHash);
        // Render the Blade template as HTML
        $html = view('email.verification', compact('user', 'verificationUrl'))->render();
        $data = [
            'replyTo' => null,
            'email' => $user->email,
            'template' => json_encode($html),
            'subject' => 'Email Verification',
        ];

        $client = new Client();
        $url = env('MS_MAIL_URL') . '/api/send-mail';
        $username = env('MS_MAIL_FANDAQAH_USER');
        $password = env('MS_MAIL_FANDAQAH_PWD');

        try {
            $response = $client->request('POST', $url, [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
                    'Content-Type' => 'application/json',
                ]
            ]);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                session()->flash('success', 'Email sent successfully.');
                return response()->json(['status' => true]);
            } else {

                throw new \Exception('Service is not available at the moment. We will retry sending the email.');
            }
        } catch (ClientException | ServerException | RequestException $e) {
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $errorMessage = json_decode($responseBody, true)['message'] ?? $e->getMessage();
            } else {
                $errorMessage = $e->getMessage();
            }

            throw new \Exception($errorMessage);
        }
        return response()->json(['status' => true]);
    }

    public function sendSmsVerification(Request $request)
    {
        $user = User::find($request->user_id);
        //validate $user->phone
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
        if (!Str::startsWith($user->phone, '+')){
            return response()->json(['status' => false, 'wrongFormat'=>true, 'message' => __('messages.invalid_phone_number')], 400);
        }
        if (!Str::startsWith($user->phone, '+966')) {
            $user->phone_verified_at = now();
            $user->save();

            return response()->json([
                'status' => true,
                'autoVerified'=>true,
                'message' => 'Phone number auto-verified'
            ], 200);
        }
        // Generate a random 4-digit code
        $verificationCode = rand(1000, 9999);

        // Save it temporarily with an expiration time
        PhoneVerificationCode::create([
            'user_id' => $user->id,
            'verification_code' => $verificationCode,
            'expires_at' => Carbon::now()->addMinutes(10),  // Code expires after 10 minutes
        ]);

        $url = env('FSMS_URL').'api/send-sms';
        $client = new Client();
        $response = $client->request('POST', $url, [
            'form_params' => [
                'Api_key' => env('FSMS_API_KEY'),
                'mobile_number' => $user->phone,
                'message' => 'Your verification code is: ' . $verificationCode,
            ]
        ]);
        $responseBody = $response->getBody()->getContents();

        // Optionally decode if the response is JSON
        $responseData = json_decode($responseBody, true);

        // For now, just return a success message (you'll replace this with actual SMS sending logic)
        if (isset($responseData['data']['success']) && $responseData['data']['success'] == true) {
            return response()->json(['status' => true,'autoVerified'=>false, 'message' => 'SMS sent successfully'], 200);
        }elseif(isset($responseData['data']['modelStateErrors'][0]['name'])){
            return response()->json(['status' => false, 'message' => $responseData['data']['modelStateErrors'][0]['name']], 500);
        }else{
            return response()->json(['status' => false, 'message' => 'Failed to send SMS'], 500);
        }
    }

    public function checkSmsVerification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|max:4',
        ]);

        $user = User::findOrFail($request->user_id);

        $verification = PhoneVerificationCode::where('user_id', $user->id)
            ->where('verification_code', $request->code)
            ->where('expires_at', '>', now())
            ->first();
        if ($verification) {
            // Mark phone as verified
            $user->phone_verified_at = now();
            $user->save();

            // Delete verification code after use
            $verification->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['error' => true], 400);
    }


    public function getUserObject(Request $request)
    {
        return response()->json(auth()->user());
    }

    public function getIntegrationControls(Request $request)
    {
        $team = Team::find(auth()->user()->current_team_id);
        return response()->json($request->type == 'shms' ? $team->suspect_shms : $team->suspect_scth);
    }

    public function checkJawalyCredentials(Request $request)
    {
        $app_id = $request->get('api_key');
        $app_sec = $request->get('api_secret');
        $app_hash = base64_encode("$app_id:$app_sec");
        $base_url = 'https://api-sms.4jawaly.com/api/v1/';
        $query = [];
        $query['page_size'] = 10; // if you want pagination how many items per page
        $query['page'] = 1; // page number
        $query['status'] = 1; // get active 1 in active 2
        $query['sender_name'] = $request->get('sender'); // search sender name full name
        $query['is_ad'] = ''; // for ads 1 and 2 for not ads
        $query['return_collection'] = 1; // if you want to get collection for all not pagination
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $base_url . 'account/area/senders?' . http_build_query($query),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . $app_hash
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function connectWithJawaly(Request $request)
    {
        $key = $request->get('key');
        $integration = new Integration();
        $integration->key = $key;
        $integration->values = json_encode($request->get('values'));
        $integration->save();

        activity()->performedOn((new IntegrationSettings()))->log(__('Integration with :KEY has been successfully done', ['key' => $key]));
        return response()->json([
            'settings' => IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first(),
            'message' => 'Fandaqah Integrated Successfully ',
            'success' => true
        ]);
    }

    public function teamSettings(Request $request)
    {
        $settings = Setting::where('team_id', auth()->user()->current_team_id)->get();

        if (count($settings)) {
            $filttered_settings = [];
            foreach ($settings as $setting) {
                if ($setting->key == 'hotel_website') {
                    $filttered_settings['hotel_website'] = $setting->value;
                }

                if ($setting->key == 'hotel_email') {
                    $filttered_settings['hotel_email'] = $setting->value;
                }

                if ($setting->key == 'hotel_phone_number') {
                    $filttered_settings['hotel_phone_number'] = $setting->value;
                }

                if ($setting->key == 'hotel_address') {
                    $filttered_settings['hotel_address'] = $setting->value;
                }

                if ($setting->key == 'hotel_en_address') {
                    $filttered_settings['hotel_en_address'] = $setting->value;
                }

                if ($setting->key == 'hotel_liceence_number') {
                    $filttered_settings['hotel_liceence_number'] = $setting->value;
                }

                if ($setting->key == 'hotel_rating') {
                    $filttered_settings['hotel_rating'] = $setting->value;
                }

                if ($setting->key == 'hotel_rating_en') {
                    $filttered_settings['hotel_rating_en'] = $setting->value;
                }

                if ($setting->key == 'city') {
                    $filttered_settings['city'] = $setting->value;
                }

                if ($setting->key == 'district') {
                    $filttered_settings['district'] = $setting->value;
                }

                if ($setting->key == 'street') {
                    $filttered_settings['street'] = $setting->value;
                }

                if ($setting->key == 'city_ar') {
                    $filttered_settings['city_ar'] = $setting->value;
                }

                if ($setting->key == 'district_ar') {
                    $filttered_settings['district_ar'] = $setting->value;
                }

                if ($setting->key == 'street_ar') {
                    $filttered_settings['street_ar'] = $setting->value;
                }

                if ($setting->key == 'commercial_register') {
                    $filttered_settings['commercial_register'] = $setting->value;
                }

                if ($setting->key == 'print_invoice_in_two_lang') {
                    $filttered_settings['print_invoice_in_two_lang'] = $setting->value;
                }

                if ($setting->key == 'print_contract_in_two_lang') {
                    $filttered_settings['print_contract_in_two_lang'] = $setting->value;
                }


                if ($setting->key == 'tin_number') {
                    $filttered_settings['tin_number'] = $setting->value;
                }

                if ($setting->key == 'postal_code') {
                    $filttered_settings['postal_code'] = $setting->value;
                }

                if ($setting->key == 'building_number') {
                    $filttered_settings['building_number'] = $setting->value;
                }
            }
        }
        return response()->json($filttered_settings);
    }

    public function saveFacilitySettings(Request $request)
    {
        $settings = Setting::where('team_id', auth()->user()->current_team_id)->get();
        if (count($settings)) {
            foreach ($settings as $setting) {
                // hotel email
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_website'
                    ],
                    [
                        'value' => $request->hotel_website ? $request->hotel_website : ''
                    ]
                );

                // Hotel Email
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_email'
                    ],
                    [
                        'value' => $request->hotel_email ? $request->hotel_email : ''
                    ]
                );

                // Hotel Phone Number
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_phone_number'
                    ],
                    [
                        'value' => $request->hotel_phone_number ? $request->hotel_phone_number : ''
                    ]
                );

                // Hotel Address
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_address'
                    ],
                    [
                        'value' => $request->hotel_address ? $request->hotel_address : ''
                    ]
                );

                // Hotel En Address
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_en_address'
                    ],
                    [
                        'value' => $request->hotel_en_address ? $request->hotel_en_address : ''
                    ]
                );

                // Hotel Liceence Number
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_liceence_number'
                    ],
                    [
                        'value' => $request->hotel_liceence_number ? $request->hotel_liceence_number : ''
                    ]
                );

                // Hotel Rating
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_rating'
                    ],
                    [
                        'value' => $request->hotel_rating ? $request->hotel_rating : ''
                    ]
                );

                // Hotel Rating EN
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'hotel_rating_en'
                    ],
                    [
                        'value' => $request->hotel_rating_en ? $request->hotel_rating_en : ''
                    ]
                );

                // City
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'city'
                    ],
                    [
                        'value' => $request->city ? $request->city : ''
                    ]
                );

                // District
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'district'
                    ],
                    [
                        'value' => $request->district ? $request->district : ''
                    ]
                );

                // Street
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'street'
                    ],
                    [
                        'value' => $request->street ? $request->street : ''
                    ]
                );

                // City
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'city_ar'
                    ],
                    [
                        'value' => $request->city_ar ? $request->city_ar : ''
                    ]
                );

                // District
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'district_ar'
                    ],
                    [
                        'value' => $request->district_ar ? $request->district_ar : ''
                    ]
                );

                // Street
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'street_ar'
                    ],
                    [
                        'value' => $request->street_ar ? $request->street_ar : ''
                    ]
                );


                // Commercial Register
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'commercial_register'
                    ],
                    [
                        'value' => $request->commercial_register ? $request->commercial_register : ''
                    ]
                );

                // Print invoice in two lang
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'print_invoice_in_two_lang'
                    ],
                    [
                        'value' => $request->print_invoice_in_two_lang ? $request->print_invoice_in_two_lang : ''
                    ]
                );

                // Print contract in two lang
                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'print_contract_in_two_lang'
                    ],
                    [
                        'value' => $request->print_contract_in_two_lang ? $request->print_contract_in_two_lang : ''
                    ]
                );

                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'tin_number'
                    ],
                    [
                        'value' => $request->tin_number ? $request->tin_number : ''
                    ]
                );

                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'postal_code'
                    ],
                    [
                        'value' => $request->postal_code ? $request->postal_code : ''
                    ]
                );

                Setting::updateOrCreate(
                    [
                        'team_id' => auth()->user()->current_team_id,
                        'key' => 'building_number'
                    ],
                    [
                        'value' => $request->building_number ? $request->building_number : ''
                    ]
                );
            }
        }

        return response()->json($request->all());
    }
    public function getReservationSettings(Request $request){
        $settings = Setting::where('team_id', auth()->user()->current_team_id)->whereIn('key',['remove_vat_from_monthly_reservations','tax'])->get();
        $tax = $settings->where('key','tax')->first()->value;
        $remove_vat = $settings->where('key','remove_vat_from_monthly_reservations')->first();
        if($remove_vat){
            $remove_vat = $remove_vat->value;
        }else{
            $remove_vat = false;
        }

        if($remove_vat == 1){
            $remove_vat = true;
        }else{
            $remove_vat = false;
        }
        if($tax == 0){
            $tax = false;
        }else{
            $tax = true;
        }
        $data = [
            'remove_vat_from_monthly_reservations' => $remove_vat,
            'tax' => $tax
        ];
        return response()->json($data);
    }

    public function getReservationServices(Request $request){
        if($request->get('all')){
            return ReservationService::where('team_id' , auth()->user()->current_team_id)->where('status',true)->whereNull('deleted_at')->orderByDesc('id')->get();
        }
        return ReservationService::where('team_id' , auth()->user()->current_team_id)->whereNull('deleted_at')->orderByDesc('id')->paginate(20);
    }

    public function getMaintenanceSettings(Request $request){
        if($request->get('all')){
            return ActionType::where('team_id' , auth()->user()->current_team_id)
                                ->where('action', 'maintenance')
                                ->whereNull('deleted_at')
                                ->orderByDesc('id')->get();
        }
        return ActionType::where('team_id' , auth()->user()->current_team_id)
                            ->where('action', 'maintenance')
                            ->whereNull('deleted_at')
                            ->orderByDesc('id')->paginate(20);
    }

    public function createActionType (Request $request) {
        $action_type = new ActionType();
        $action_type->action = $request->get('action');
        $action_type->name_en = $request->get('name_en');
        $action_type->name_ar = $request->get('name_ar');
        $action_type->status = $request->get('status');
        $action_type->team_id = $request->get('team_id');
        try {
            $action_type->save();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'failed' , 'exception' => $th->getMessage()], 500);
        }
    }

    public function getActionTypes (Request $request) {
        $type = $request->get('type');
        $fetchAll = $request->get('fetchAll');
        $team_id = auth()->user()->current_team_id;

        if(isset($fetchAll) && $fetchAll == true) {
            $actionTypes = ActionType::where('team_id', $team_id)
            ->where('action',$type)
            ->withTrashed()
            ->get();
            return response()->json([
                'actionTypes' => $actionTypes
            ]);
        }

        $actionTypes = ActionType::where('team_id', $team_id)
                    ->where('action',$type)
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->get();
        return response()->json([
            'actionTypes' => $actionTypes
        ]);
    }

    public function editActionType (Request $request) {
        $action_type =  ActionType::findOrFail($request->id);
        $action_type->action = $request->get('action');
        $action_type->name_en = $request->get('name_en');
        $action_type->name_ar = $request->get('name_ar');
        $action_type->status = $request->get('status');
        $action_type->team_id = $request->get('team_id');
        try {
            $action_type->save();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'failed' , 'exception' => $th->getMessage()], 500);
        }
    }

    public function deleteActionType (Request $request) {
        $action_type =  ActionType::findOrFail($request->id);
        if($action_type->delete()) {
            return response()->json([], 200);
        } else {
            return response()->json([
                'is_attached' => true
            ], 200);
        }
    }

    public function addReservationService(StoreReservationServiceRequest $request){

        $reservationService = new ReservationService();
        $reservationService->team_id = auth()->user()->current_team_id;
        $reservationService->user_id = auth()->user()->id;
        $reservationService->name_ar = $request->name_ar;
        $reservationService->name_en = $request->name_en;
        $reservationService->status = $request->status;
        try {
            $reservationService->save();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'failed' , 'exception' => $th->getMessage()]);
        }

    }


    public function editReservationService(UpdateReservationServiceRequest $request){

        $reservationService =  ReservationService::find($request->id);
        $reservationService->name_ar = $request->name_ar;
        $reservationService->name_en = $request->name_en;
        $reservationService->status = $request->status;
        try {
            $reservationService->save();
            return response()->json(['status' => 'success']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'failed' , 'exception' => $th->getMessage()]);
        }

    }

    public function deleteReservationService(Request $request){
        $checkReservationServiceMapper = ReservationServiceMapper::where('reservation_service_id',$request->get('id'))->get();
        if(count($checkReservationServiceMapper)){
            return response()->json(['is_attached' => true]);
        }
        $reservationService = ReservationService::find($request->get('id'));
        $reservationService->delete();
        return response()->json(['is_attached' => false ]);
    }
    public function getGeneralSettings(Request $request)
    {
        // dd(HandlersSettings::get('hotel_address'));
        // return  HandlersSettings::get('hotel_address') ;
        // return all values in handlers settings
       // from settings table return hotel_address hotel_address hotel_phone_number where team_id = auth()->user()->current_team_id
       $settings = Setting::where('team_id', auth()->user()->current_team_id)->whereIn('key',['hotel_address','hotel_email','hotel_phone_number'])->get();
        // return response()->json([
        //     'AddressLine' => HandlersSettings::get('hotel_address'),
        //     'PostalCode' => 333344,
        //     'CountryName' => 'SA',
        //     'NotificationEmail' => HandlersSettings::get('hotel_address'),
        //     'PhoneNumber' => HandlersSettings::get('hotel_phone_number')
        // ]);
        $data = [
            'AddressLine' => $settings->where('key','hotel_address')->first()->value,
            'PostalCode' => 333344,
            'CountryName' => 'SA',
            'NotificationEmail' => $settings->where('key','hotel_email')->first()->value,
            'PhoneNumber' => $settings->where('key','hotel_phone_number')->first()->value
        ];
        return response()->json($data);
    }


    public function connectWithZatcaPhaseTwo(Request $request) {

        $team = auth()->user()->teams()->where('id', auth()->user()->current_team_id)->first();

        $settings = (object) Setting::where('team_id', auth()->user()->current_team_id)->whereIn('key',['hotel_address','hotel_email','hotel_phone_number', 'tax_number', 'city', 'district', 'street', 'commercial_register'])->pluck('value', 'key')->all();

        //must set tax number in general settings
        if(!isset($settings->tax_number)) {
            return response()->json([
                'message' => 'tax number is required'
            ], 500);
        }

        $otp = $request->get('otp');
        $key = $request->get('key');

        $org = auth()->user()->getSupplierEGS();
        $otp_verifier = new VerifyOtp($org);
        $response = $otp_verifier->verifyOtp($otp);
        if(isset($response->data->binarySecurityToken)) {

            $integration = new Integration();
            $integration->key = $key;
            $integration->team_id = auth()->user()->current_team_id;
            $integration->values = json_encode(Array (
                'username' => $response->data->binarySecurityToken,
                'password' => $response->data->secret
            ));

            $integration->save();

            activity()->performedOn((new IntegrationSettings()))->log(__('Integration with :KEY has been successfully done', ['key' => $key]));

            return response()->json([
                'settings' => IntegrationSettings::with('integration')->where(['key' => $key, 'status' => true])->first(),
                'message' => 'Fandaqah Integrated Successfully',
                'success' => true
            ],200);

        }

        activity()->performedOn((new IntegrationSettings()))->log(__('Integration with :KEY has been failed', ['key' => $key]));

        return response()->json([
            'message' => $response->data->message ?? "Failed",
            'success' => false
        ], 500);
    }

}
