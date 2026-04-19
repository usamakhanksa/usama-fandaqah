<?php

namespace App;

use FrittenKeeZ\Vouchers\Concerns\HasVouchers;
use Illuminate\Database\Eloquent\Model;
use App\Scopes\TeamScope;

class Subscription extends Model
{
    use HasVouchers;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "team_subscriptions";

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'ends_at',
    ];

    protected $casts = [
        'ends_at'   =>  'datetime',
        'trial_ends_at' =>  'datetime'
    ];

    protected $with = ['team'];

    protected static function boot()
    {
        parent::boot();
        //static::addGlobalScope(new TeamScope());
        static::creating(function (Subscription $item) {
            $item->name = $item->stripe_plan;
            $item->stripe_id = rand();
            $item->quantity = 1;
        });

        static::created(function (Subscription $item) {
            $item->hotel->forceFill([
                'current_billing_plan' => $item->stripe_plan,
                'contract_price' => $item->contract_price,
                'contract_note' => $item->contract_note,
                'trial_ends_at' => $item->trial_ends_at,
                'ends_at' => $item->ends_at,
            ])->save();
        });

        static::updated(function (Subscription $item) {

            /*
             * @note : Ternary Conditional is not working that's why am using if - else
             */
            if($item->stripe_plan  == 'team-basic'){
                $item->hotel->forceFill([
                    'current_billing_plan' => $item->stripe_plan,
                    'contract_price' => $item->contract_price,
                    'contract_note' => $item->contract_note,
                    'trial_ends_at' => null ,
                    'ends_at' => $item->ends_at,
                ])->save();
            }elseif ($item->stripe_plan  == 'trial'){
                $item->hotel->forceFill([
                    'current_billing_plan' => $item->stripe_plan,
                    'contract_price' => $item->contract_price,
                    'contract_note' => $item->contract_note,
                    'trial_ends_at' => $item->trial_ends_at ,
                    'ends_at' => null,
                ])->save();
            }

        });
    }

    /**
     * Get the model related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'team_id');
    }

    /**
     * Get the team that owns the subscription.
     */
    public function team()
    {
        return $this->user();
    }

    /**
     * Get the team that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(Hotel::class, 'team_id');
    }
}
