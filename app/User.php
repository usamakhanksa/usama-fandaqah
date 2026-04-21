<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Spark\CanJoinTeams;
use Laravel\Spark\Contracts\Interactions\Settings\Teams\AddTeamMember as AddTeamMemberContract;
use Laravel\Spark\Spark;
use Laravel\Spark\User as SparkUser;
use Laravelista\Comments\Commenter;
use Pktharindu\NovaPermissions\Traits\HasRoles;
use App\Traits\LogsActivity;
use Watson\Rememberable\Rememberable;

class User extends SparkUser
{
    use Rememberable;
    use HasRoles;
    use Commenter;
    use CanJoinTeams;
    use Notifiable;
    use LogsActivity;
    use HasApiTokens;
    use SoftDeletes;

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'role_user');
    }

    public function teams()
    {
        return $this->belongsToMany(\App\Team::class, 'team_users', 'user_id', 'team_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'photo_url',
        'email',
        'phone',
        'extra_billing_information',
        'current_team_id',
        'password'
    ];

    public static function boot()
    {
        static::created(function ($query) {
            if (auth()->check()) {
                Spark::interact(AddTeamMemberContract::class, [
                    auth()->user()->current_team, $query, 'member'
                ]);
                $role = Role::where('slug', 'Staff')->first();
                if ($role) {
                    $user = $query;
                    if (!$user->isAdmin()) {
                        $user->roles()->attach($role);
                    }
                }
            }
        });
        parent::boot();
    }

    public function getPhotoUrlAttribute($value)
    {
        return $value;
    }

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'authy_id',
        'country_code',
        'two_factor_reset_code',
        'card_brand',
        'card_last_four',
        'card_country',
        'billing_address',
        'billing_address_line_2',
        'billing_city',
        'billing_zip',
        'billing_country',
        'extra_billing_information',
        'device_token'
    ];

    protected $appends = [
        // 'roles_text'
        'digital_signature'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'uses_two_factor_auth' => 'boolean',
        'extra_billing_information' => 'json'
    ];

    /**
     * Determines if the User is a Super admin
     * @return null
     */
    public function isSuperAdmin()
    {
        return $this->hasRole('super-admin');
    }
    
 

    public function isHotelManager()
    {
        foreach ($this->roles()->get() as $role) {
            if ($role->slug == 'hotel_manager') {
                return true;
            }
        }

        return false;
    }

    public function isAccHo()
    {
        foreach ($this->roles()->get() as $role) {
            if ($role->slug == 'acc_ho') {
                return true;
            }
        }

        return false;
    }



    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'App.User.' . $this->id;
    }

    public function isAdmin()
    {
        foreach ($this->roles()->get() as $role) {
            if ($role->slug == 'admin') {
                return true;
            }
        }

        return false;
    }

    public function addMedia($file)
    {
        return $this->parentAddMedia($file)
            ->usingFileName($file->hashName());
    }

    public function getRolesTextAttribute()
    {
        $roles = [];
        /** @var Role $role */
        foreach ($this->roles as $role) {
            if (!in_array($role->name, $roles)) {
                $roles[] = $role->name;
            }
        }
        return implode(',', $roles);
    }

    public function routeNotificationForOneSignal()
    {
        return $this->device_token;
    }

    public function hasDeviceToken()
    {
        if ($this->device_token) {
            return false;
        } else {
            return false;
        }
    }
    public function getTeamLogo() {
        return $this->currentTeam()->photo_url ?? null;
    }

    public function getSupplierEGS() {
        $team = auth()->user()->teams()->where('id', auth()->user()->current_team_id)->first();
       
        $settings = (object) \App\Setting::where('team_id', auth()->user()->current_team_id)->whereIn('key',[
            'hotel_address',
            'hotel_email',
            'hotel_phone_number', 
            'tax_number', 
            'city', 
            'city_ar', 
            'district',
            'district_ar',
            'street', 
            'street_ar',
            'postal_code',
            'building_number',
            'commercial_register',
            'tin_number'
        ])->pluck('value', 'key')->all();
    
        if(!isset($settings->tax_number)) {
            return null;    
        }

        $branch_name = $settings->branch ?? "Test branch";
        $city = "";
        $district = "";
        $street = "";
        $postal_code = "";
        $building_number = "";

        if(isset($settings->city)) {
            $city .= $settings->city;  
        }

        if(isset($settings->city_ar)) {
            $city .= " | $settings->city_ar";
        }

        if(isset($settings->district)) {
            $district .= $settings->district;  
        }

        if(isset($settings->district_ar)) {
            $district .= " | $settings->district_ar";
        }

        if(isset($settings->street)) {
            $street .= $settings->street;  
        }

        if(isset($settings->street_ar)) {
            $street .= " | $settings->street_ar";
        }

        if(isset($settings->tin_number)) {
            $branch_name = $settings->tin_number;
        }

        if(isset($settings->postal_code)) {
            $postal_code = $settings->postal_code;
        }
        
        if(isset($settings->building_number)) {
            $building_number = $settings->building_number;
        }
        
        
        return Array (
            "model" => "Team-".auth()->user()->current_team_id,
            "uuid" => '1-' . auth()->user()->current_team_id . '-' . $settings->tax_number,
            "solution_name" => "Sol-".auth()->user()->current_team_id,
            "vat_number" => $settings->tax_number ?? "",
            "location" => Array (
                "city" => $city,
                "city_subdivision" => $district,
                "street" => $street,
                "plot_identification" => "",
                "building" => $building_number ?? "",
                "postal_zone" => $postal_code ?? "",
            ),
            "branch_industry" => "hospitality",
            "branch_name" =>  $branch_name,
            "org_name" => $team->name,
            "taxpayer_name" => $team->name,
            "CRN_number" => $settings->commercial_register ?? "",
            "production" => env('APP_ENV') == 'production' ? "1" : "0",
            "team_id" => auth()->user()->current_team_id
        );

    }

    public function digital_signature()
    {
        return $this->hasOne('App\DigitalSignature', 'user_id', 'id')->where('type', 'user');
    }


    public function getDigitalSignatureAttribute()
    {
        return $this->digital_signature()->latest()->first();
    }
}
