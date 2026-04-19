<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Jobs\TeamDeleted;
use App\Traits\HasWallet;
use App\Handlers\Settings;
use App\Interfaces\Wallet;
use App\IntegrationSettings;
use Laravel\Spark\CanJoinTeams;
use Illuminate\Support\Facades\DB;
use Laravel\Spark\Team as SparkTeam;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends SparkTeam implements Wallet
{
    use Rememberable, CanJoinTeams, HasWallet, SoftDeletes;

    public static $plans = ['trial' => 'Trial', 'team-free' => 'Free', 'team-basic' => 'Basic'];

    protected $fillable = [
        'slug',
        'enable_website',
        'private_domain',
        'enable_private_domain',
        'private_domain_status',
        'name',
        'owner_id',
        'country_id'
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected $with = ['country'];

    protected $appends = [
        'website_url',
        'integration_scth',
        'integration_shms',
        'last_subscription',
        'integration_shomoos_version_one',
        'integration_zatca_phase_two',
        'check_integration_zatca_phase_two_enable',
        'check_calculate_price_by_day_enable'
    ];

    // protected $with = ['alraedahRequest'];

    public static function boot()
    {
        parent::boot();

        static::created(function ($query) {

            $role = new \App\Role();
            $role->name = 'Admin';
            $role->slug = 'admin';
            $role->deletable = 0;
            $role->team_id = $query->id;



            $role->save();
            foreach (config('novapermissions.permissions') as $key => $permissions) {
                $role->grant($key);
            }

            $user = User::find($query->owner_id);
            $user->roles()->attach($role);

            // Staff roles and permissions
            $staff = new \App\Role();
            $staff->name = 'Staff';
            $staff->slug = 'staff';
            $staff->team_id = $query->id;
            $staff->deletable = 0;
            $staff->save();


            $excluded_permissions = ['view settings', 'view reports', 'view financial'];

            foreach (config('novapermissions.permissions') as $key => $permissions) {
                if (!in_array($key, $excluded_permissions)) {
                    $staff->grant($key);
                }
            }


            $housekeeping = new \App\Role();
            $housekeeping->name = 'Housekeeping';
            $housekeeping->slug = 'housekeeping';
            $housekeeping->team_id = $query->id;
            $housekeeping->deletable = 0;
            $housekeeping->save();
            // attach view reservation permission to housekeeping role
            $housekeeping->grant('view reservations');


            if (is_null($query->trial_ends_at)) {
                $now = new Carbon();
                $now->addDays(15);
                $query->trial_ends_at = $now;
                $query->save();
            }
            if (is_null($query->current_billing_plan)) {
                $query->current_billing_plan = 'trial';
                $query->save();
            }
        });

        static::deleted(function (Team $team) {
            dispatch(new TeamDeleted($team));
        });
    }

    public function getPhotoUrlAttribute($value)
    {
        return $value ?? null;
    }

    public function counter()
    {
        return $this->hasOne(TeamCounter::class);
    }

    /**
     * Get the all Team integrations.
     */
    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    /**
     * Get the all Team reservations.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the all Team reservations.
     */
    public function occupieds(): HasMany
    {
        return $this->hasMany(Occupied::class);
    }

    /**
     * Get the all Team units.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get the all Team roles.
     */
    public function roles()
    {
        return $this->hasMany(\Pktharindu\NovaPermissions\Role::class, 'team_id');
    }

    public function setting()
    {
        return $this->hasMany(Setting::class, 'team_id', 'id');
    }
    public function websiteSetting()
    {
        return $this->hasOne(WebsiteSetting::class, 'team_id', 'id');
    }

    public function getWebsiteUrlAttribute()
    {
        if ($this->enable_website) {

            if ($this->enable_private_domain && $this->private_domain && $this->private_domain_status == 'installed') {
                $domain = "https://{$this->private_domain}";
            } else {
                $domain = "https://{$this->slug}." . config('app.corneer_url');
            }
            return url($domain);
        }

        return null;
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function onlineReservations(): HasMany
    {
        return $this->hasMany(OnlineReservation::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServicesCategory::class);
    }

    public function reservationAutoRenewKey()
    {
        return  DB::table('settings')->where('key', '=', 'automatic_renewal_of_reservations')->where('team_id', '=', $this->id)->value('value');
    }

    public function hotelEmail()
    {
        return  DB::table('settings')->where('key', '=', 'hotel_email')->where('team_id', '=', $this->id)->value('value');
    }

    public function reservationDayEndKey()
    {
        return DB::table('settings')->where('key', '=', 'day_end')->where('team_id', '=', $this->id)->value('value');
    }


    public function vat()
    {
        return DB::table('settings')->where('key', '=', 'tax')->where('team_id', '=', $this->id)->value('value');
    }

    public function ewa()
    {
        return DB::table('settings')->where('key', '=', 'accommodation_tax')->where('team_id', '=', $this->id)->value('value');
    }

    public function ttx()
    {
        return DB::table('settings')->where('key', '=', 'tourism_tax')->where('team_id', '=', $this->id)->value('value');
    }

    public function day_start()
    {
        return DB::table('settings')->where('key', '=', 'day_start')->where('team_id', '=', $this->id)->value('value');
    }

    public function day_end()
    {
        return DB::table('settings')->where('key', '=', 'day_end')->where('team_id', '=', $this->id)->value('value');
    }

    public function scopeCityId($query, $id)
    {
        $query->where('city_id', $id);
    }

    public function scopeOfPrivateDomain($query, $domain)
    {
        return $query->where('enable_website', true)
            ->where('private_domain_status', 'installed')
            ->where('private_domain', $domain);
    }

    public function scopeNewPrivateDomains($query, $status = 'new')
    {
        return $query->where('enable_website', true)
            ->where('private_domain_status', $status)
            ->where('private_domain', '!=', null);
    }

    public function scopeHasNotBillsAccount($query)
    {
        return $query->where('sure_bills_active', false);
    }

    public function scopeDeletedPrivateDomains($query)
    {
        return $query->where('private_domain_status', 'deleted')
            ->where('private_domain', '!=', null);
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function attachedHotels()
    {
        return $this->belongsToMany(Team::class, 'related_teams', 'team_id', 'related_id')->with('city');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'current_team_id');
    }

    public function integrationUnifonic()
    {
        return $this->hasOne(Integration::class, 'team_id')->where('key', '=', 'unifonic');
    }

    public function getintegrationScthAttribute()
    {
        return (bool) Settings::checkIntegration('SCTH', $this->id);
    }

    public function getintegrationShmsAttribute()
    {
        return (bool) Settings::checkIntegration('SHMS', $this->id);
    }

    public function getIntegrationZatcaPhaseTwoAttribute() 
    {
        return Integration::where('team_id' , $this->id)
       ->where('key' , 'ZatcaPhaseTwo')
       ->whereNull('deleted_at')
       ->exists();
    }

    public function getCheckIntegrationZatcaPhaseTwoEnableAttribute() 
    {
        return $this->enable_zatca_phase_two == 1;
    }

    public function getCheckCalculatePriceByDayEnableAttribute() 
    {
        $setting = Setting::where('key', 'calculate_price_by_day')
        ->where('team_id', $this->id)
        ->where('value', '1')
        ->latest('created_at')
        ->first();
        if($setting && $setting['value'] == '1') {
            return true;
        }
        return false;
    }

    public function getBaseUrl()
    {
        if ($this->private_domain_status == 'installed' && $this->private_domain) {
            $base_url = $this->private_domain;
        } else {
            if ($this->slug) {
                $base_url = $this->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');
            } else {
                $base_url = $this->id . '.' . env('CUSTOMER_WEBSITE_DOMAIN');
            }
        }

        return 'https://' . $base_url;
    }

    public function createBillsAccount()
    {

        if ($this->sure_bills_active) {
            return true;
        }

        // get base url
        $base_url = $this->getBaseUrl();

        // create a new sure bills account
        $client = new Client(['base_uri' => config('app.bills_url')]);
        $params = [
            'form_params' => [
                'business_name' => $this->name,
                'name'          => $this->owner ? $this->owner->name : 'NOOWNER',
                'email'         => $this->id . '@fandaqah.com',
                'mobile'        => $this->id,
                'redirect_url'  => $base_url . '/callback',
                'webhook_url'   => $base_url . '/callback'
            ],
            'verify' => false,
            'headers' => [
                'Accept'     => 'application/json'
            ]
        ];
        $engine = $client->request('POST', '/api/v1/fandaqah-register', $params);
        $payload = json_decode($engine->getBody()->getContents(), true);

        if (isset($payload['client_id']) && isset($payload['secret']) && isset($payload['webhook_secret'])) {
            $this->sure_bills_active         = true;
            $this->sure_bills_client_id      = $payload['client_id'];
            $this->sure_bills_secret         = $payload['secret'];
            $this->sure_bills_webhook_secret = $payload['webhook_secret'];
            $this->save();

            return true;
        }

        return false;
    }

    public function updateBillsAccountRedirectUrls()
    {

        if (!$this->sure_bills_active) {
            return $this->createBillsAccount();
        }

        // get urls
        $base_url = $this->getBaseUrl();

        // create a new sure bills account
        $client = new Client(['base_uri' => config('app.bills_url')]);
        $params = [
            'form_params' => [
                'client_id'      => $this->sure_bills_client_id,
                'secret'         => $this->sure_bills_secret,
                'webhook_secret' => $this->sure_bills_webhook_secret,
                'redirect_url'   => $base_url . '/callback',
                'webhook_url'    => $base_url . '/callback'
            ],
            'verify' => false,
            'headers' => [
                'Accept'     => 'application/json'
            ]
        ];
        $engine = $client->request('POST', '/api/v1/fandaqah-update-redirect', $params);
        $payload = json_decode($engine->getBody()->getContents(), true);

        return $payload;
    }

    public function deleteIngerationWithScth()
    {
        activity()->performedOn((new IntegrationSettings))->log(__('Integration with :KEY has been successfully removed', ['key' => 'SCTH']));
        return Integration::where('key', '=', 'SCTH')->where('team_id', $this->id)->delete();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function alraedahRequest()
    {
        return $this->hasOne(FormIntegration::class, 'team_id')->where('integration_name', 'alraedah-finance')->where('status', 'pending');
    }


    protected function getLastSubscriptionAttribute()
    {
        return $this->teamSubscriptions()->orderByDesc('created_at')->first();
    }

    public function teamSubscriptions()
    {
        return $this->hasMany(Subscription::class, 'team_id');
    }

    public function terms()
    {
        return $this->hasMany(Term::class, 'team_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'team_users');
    }

    /**
     * This will save our ass -_- to survive with Shomoos Version One until the client decided to change to the new integration 
     *
     * @return void
     */
    public function getIntegrationShomoosVersionOneAttribute()
    {
       return Integration::where('team_id' , $this->id)
       ->where('key' , 'SHMS')
       ->whereJsonContains('values->SPSCID', null)
       ->whereNull('deleted_at')
       ->exists();
    }

    public function reservationDefaultStatusKey()
    {
        return  DB::table('settings')->where('key', '=', 'reservation_default_status')->where('team_id', '=', $this->id)->value('value');
    }

    public function tax_number()
    {
        return DB::table('settings')->where('key', '=', 'tax_number')->where('team_id', '=', $this->id)->value('value');
    }

    public function address() {
        return [
            'city' => DB::table('settings')->where('key', '=', 'city')->where('team_id', '=', $this->id)->value('value'),
            'city_ar' => DB::table('settings')->where('key', '=', 'city_ar')->where('team_id', '=', $this->id)->value('value'),
            'district' => DB::table('settings')->where('key', '=', 'district')->where('team_id', '=', $this->id)->value('value'),
            'district_ar' => DB::table('settings')->where('key', '=', 'district_ar')->where('team_id', '=', $this->id)->value('value'),
            'street' => DB::table('settings')->where('key', '=', 'street')->where('team_id', '=', $this->id)->value('value'),
            'street_ar' => DB::table('settings')->where('key', '=', 'street_ar')->where('team_id', '=', $this->id)->value('value'),
        ];
    }
}
