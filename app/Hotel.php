<?php

namespace App;

use App\Integration;
use App\Jobs\TeamDeleted;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes ;

class Hotel extends Model
{
    use SoftDeletes ;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "teams";

    /**
     * The attributes that fillable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'photo_url', 'trial_ends_at', 'application_id', 'ends_at'];


    protected $casts = [
        'trial_ends_at' =>  'datetime',
        'ends_at' =>  'datetime'
    ];

    protected $appends = ['integration','integration_with_scth','integration_with_shms'];
    protected $with = ['owner'];
    protected static function boot()
    {
        parent::boot();

        // Creation event  setting default plan and plan ends after
        static::creating(function ($query) {

            $current_date= now() ;
            $query->current_billing_plan = 'trial';
            $query->trial_ends_at = $current_date->addDays(14);
        });

        static::deleted(function (Hotel $team) {
            dispatch(new TeamDeleted($team));
        });
    }

    public static $plans = ['trial' =>  'Trial', 'team-free' =>  'Free', 'team-basic' =>  'Basic'];

    /**
     * user owner
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function owner()
    {
        return $this->belongsTo(User::class,'owner_id');
    }

    /**
     *  team users
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function users()
    {
        return $this->belongsToMany(User::class,'team_users', 'team_id', 'user_id');
    }

    /**
     *  hotel units
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function units()
    {
        return $this->hasMany(Unit::class, 'team_id');
    }

    /**
     *  hotel customer
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function customers()
    {
        return $this->hasMany(Customer::class, 'team_id');
    }

    /**
     *  hotel reservation
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'team_id');
    }

    public function subscription($subscription = 'default')
    {
        return $this->subscriptions->sortByDesc(function ($value) {
            return $value->created_at->getTimestamp();
        })->first(function ($value) use ($subscription) {
            return $value->name === $subscription;
        });
    }

    /**
     * Get all of the subscriptions for the Stripe model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'team_id')->orderBy('created_at', 'desc');
    }


    public function scopeBasicActive($query){
        return $query->where('current_billing_plan' , 'team-basic')->where('ends_at' , '>' , now())->get() ;
    }

    public function scopeBasicEnded($query){
        return $query->where('current_billing_plan' , 'team-basic')->where('ends_at' , '<' , now())->get() ;
    }


    public function scopeTrialEnded($query){
        return $query->where('current_billing_plan' , 'trial')->where('trial_ends_at' , '<' , now())->whereNull('deleted_at')->get();
    }

    public function scopeTrialActive($query){
        return $query->where('current_billing_plan' , 'trial')->where('trial_ends_at' , '>' , now())->whereNull('deleted_at')->get() ;
    }


    public function integrationScth(){
        return $this->hasOne(Integration::class , 'team_id')->where('key' , '=' , 'SCTH');
    }

    public function integrationShms(){
        return $this->hasOne(Integration::class , 'team_id')->where('key' , '=' , 'SHMS');
    }




    public function getIntegrationAttribute(){
        return Integration::where('team_id' , $this->id)->where('key' , 'SCTH')->first();
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

     public function type()
     {
         return $this->belongsTo(HotelType::class, 'type_id', 'id');
     }

     public function getIntegrationWithScthAttribute(){

       return  \App\Integration::where('team_id' , $this->id)->where('key' , 'SCTH')->exists();
     }

    public function getIntegrationWithShmsAttribute()
    {

        return \App\Integration::where('team_id', $this->id)->where('key', 'SHMS')->exists();
    }
    public function related()
    {
        return $this->belongsToMany(Hotel::class, 'related_teams', 'team_id', 'related_id');
    }

}
