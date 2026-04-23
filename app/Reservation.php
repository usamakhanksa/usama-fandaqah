<?php

namespace App;

use App\Helpers\CheckDatesLang;
use App\Scopes\TeamScope;
use Carbon\Carbon;
use App\Promissory;
use GuzzleHttp\Client;
use App\Traits\HasTeam;
use Carbon\CarbonPeriod;
use App\Jobs\ProcessSCTH;
use App\Interfaces\Wallet;
use App\Traits\HasWalletFloat;
use App\Interfaces\WalletFloat;
use Illuminate\Support\Facades\DB;
use App\Events\ReservationCanceled;
use Vinkla\Hashids\Facades\Hashids;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Surelab\Settings\ValueObjects\SettingRegister;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Log;

class Reservation extends Model implements  Wallet, WalletFloat
{
    use Rememberable, SoftDeletes, HasWalletFloat, HasTeam, LogsActivity;

    /**
     * reservation statuses
     */
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_PENDING = 'pending';


    // action types
    const ACTION_CHECKEDIN = 'checked_in';
    const ACTION_CHECKEDOUT = 'checked_out';
    const ACTION_CREATERESERVATION = 'create_reservation';
    const ACTION_UPDATERESERVATION = 'update_reservation';
    const ACTION_UPDATERESERVATIONFROMCOMMAND = 'update_reservation_from_command';
    const ACTION_CANCELED = 'cancel_reservation';
    const ACTION_RESERVATION_TIMEOUT = 'timeout_reservation';
    const ACTION_RESERVATION_AWAITING_CONFIRMATION = 'awaiting_confirmation_reservation';
    const ACTION_RESERVATION_AWAITING_PAYMENT = 'awaiting_payment_reservation';
    const ACTION_UPDATERESERVATIONCUSTOMER = 'update_reservation_customer';
    const ACTION_UPDATE_GROUP_RESERVATION_PRICE_FROM_MODAL = 'update_grp_price_from_modal';

    const RENT_TYPE_DAILY = 1;
    const RENT_TYPE_MONTHLY = 2;

    protected $guarded = ['id'];
    protected $with = ['unit','reservationFreeServices'];
    protected static $logName = 'reservation';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributes = [
                    'total_price',
                    'customer_id',
                    'status' ,
                    'date_in' ,
                    'date_out' ,
                    'checked_in' ,
                    'checked_out' ,
                    'unit.unit_number' ,
                    'rent_type',
                    'customer.name',
                    'customer.phone' ,
                    'customer.email',
                    'customer.customer_type_string',
                    'customer.id_type_string',
                    'customer.nationality_string',
                    'customer.id_number'
                ];


    protected $appends = [
        'nights',
//        'global_check_in',
//        'global_check_out',
        'duration' ,
//        'withdraw_sum' ,
//        'deposit_sum' ,
//        'services_sum',
//        'services_without_taxes_sum',
//        'services_taxes_sum',
//        'ttx_total_sum',
//        'vat_total_sum',
        'hash_id',
        'customer_name'
    ];

    protected $casts = [
        'prices'    =>  'array',
        'old_prices'    =>  'array',
        'special_prices_ids' => 'array',
        'offers_ids' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        // auto-sets values on creation
        static::creating(function ($query) {
            if(empty($query->team_id))
                $query->team_id = auth()->user()->current_team_id;
        });
        static::addGlobalScope(new TeamScope());
        //if(auth()->check()) {
            //dd(auth()->user());
            // static::addGlobalScope('team_id', function (Builder $builder) {
            //     $builder->where('team_id', '=', request()->get('current_team_id', auth()->user()->current_team_id))->where('team_id', '!=',null);
            // });
        //}
    }


    public function promissory()
    {
        return $this->hasOne(Promissory::class);
    }

    public function reservation_guests()
    {
        return $this->belongsToMany(Guest::class , 'customer_guest_reservation','reservation_id' , 'guest_id')->distinct();
    }
    /**
     * Returns all guest for this model.
     * This is legacy -_-
     */
    public function guests()
    {
        return $this->hasMany(Guest::class)->whereNotNull('reservation_id');
    }

    public function adults(){
        // return $this->hasMany(ReservationGuests::class, 'reservation_id','id')->whereNotIn('relation_type' , [1,2]);
        return $this->reservation_guests()->whereNotIn('relation_type' , [1,2]);
    }

    public function childrens(){
        // return $this->hasMany(ReservationGuests::class, 'reservation_id','id')->whereIn('relation_type' , [1,2]);
        return $this->reservation_guests()->whereIn('relation_type' , [1,2]);
    }

    /**
     * @return mixed
     */
    public function customer(){
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    /**
     * @return mixed
     */
    public function unit(){
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(){
        return $this->belongsTo(User::class,'created_by')->withTrashed();
    }

    /**
     * Scope a query to get only Between Date Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDate($query, $date)
    {
        return $query->whereRaw('? between date_in and date_out', [$date->format('Y-m-d')]);
    }


    /**
     * Intersection
     *
     * @param [type] $query
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    public function scopeIntersection($query, $start , $end)
    {
        return $query->whereRaw('? between date_in and date_out', [$start->format('Y-m-d')])
                        ->orWhereRaw('? between date_in and date_out', [$end->format('Y-m-d')]);
    }

    /**
     * Scope a query to get only Between Date Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrBetweenDate($query, $date)
    {
        return $query->orWhereRaw('? between date_in and date_out', [$date->format('Y-m-d')]);
    }




    /**
     * Scope a query to get only Between Date Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDateBetween($query, $date)
    {
        return $query->where('date_in', '<=', $date)->where('date_out', '>', $date);
    }

    public function scopeWhereDateTimeBetween($query, $date)
    {
        return $query->where('date_in_time', '<=', $date)->where('date_out_time', '>', $date);
    }

    public function scopeWhereStartDateBetween($query, $date)
    {
        return $query->where('date_in', '<', $date)->where('date_out', '>', $date);
    }

    /**
     * Scope a query to get only Between Date Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereDateBetweenDates($query, $date_in, $date_out)
    {

        return $query
        ->where('date_in', '>=', $date_in->format('Y-m-d'))
        ->Where('date_out', '<=', $date_out->format('Y-m-d'))
        // ->whereRaw('? between date_in and date_out', [$date_in->format('Y-m-d')]);
        ;
    }

    /**
     * Scope a query to get only Departure Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeparture($query, $date)
    {
        return $query->where('date_out', '=', $date);
    }


    /**
     * Scope a query to get only Departure Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDepartureWithCheckedOut($query, $date)
    {
        // dd( $date);
        return $query
            ->where(function($query) use($date){
                $query->where('date_out', '=', $date)
                    ->whereNull('checked_out');
            })
            ->orWhere(function($query) use($date){
                $query->whereDate('checked_out', $date);
            });
    }

    /**
     * Scope a query to get only Arrival Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArrival($query, $date)
    {
        return $query->where('date_in', '=', $date)->where(['status'=>'confirmed']);
    }

    /**
     * Scope a query to get Over Out Date Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverOutDate($query)
    {
        return $query->where('date_out', '<', Carbon::now()->format('Y-m-d'))
            ->whereNull('checked_out')
            ->whereNotNull('checked_in');
    }

    /**
     * Scope a query to filter By Unit Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUnitNumber($query, $value)
    {
        return $query->whereHas('unit' , function($unit) use($value){
             $unit->where('unit_number' , $value);
        });
    }


    public function scopeContractByUnitNumber($query, $value){

        return $query->where('unit_id' , $value);
    }

    /**
     * Scope a query to filter By Unit Id.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUnitId($query, $value)
    {
        return $query->whereHas('unit' , function($unit) use($value){
             $unit->where('id' , $value);
        });
    }

    /**
     * Scope a query to filter by Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByNumber($query, $value)
    {
        return $query->where('number' , $value);
    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCustomerIdNumber($query, $value)
    {
        return $query->whereHas('customer', function ($customer) use($value) {
             $customer->where('id_number' , 'LIKE' , "%$value%");
        });
    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, $value)
    {
        switch ($value){
            case 'pending':
                 return $query->where('checked_in' , null )->where('checked_out' , null);
                break;
            case 'checked_in':
                return $query->where('checked_in' , '!=' , null )->where('checked_out' , null);
                break;
            case 'checked_out':
                return $query->where('checked_out'  , '!=' , null );
                break;
            case 'canceled':
                return $query->where('status'  , '='  , 'canceled');
                break;
        }
    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndebtednessType($query, $value , $reservation_type)
    {
       if($reservation_type == 'single'){
            switch ($value){
                case 'creditor':
                    return $query->whereHas('wallet',function($wallet){
                        $wallet->where('balance' , '>' , 0);
                    });
                    break;
                case 'debtor':
                    return $query->whereHas('wallet',function($wallet){
                        $wallet->where('balance' , '<' , 0);
                    });
                    break;
            }
       }else{
            switch ($value){
                case 'creditor':
                    return $query->whereHas('groupReservationBalanceMapper',function($grp){
                        $grp->where('balance' , '>' , 0);
                    });
                    break;
                case 'debtor':
                    return $query->whereHas('groupReservationBalanceMapper',function($grp){
                        $grp->where('balance' , '<' , 0);
                    });
                    break;
            }
       }

    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRentType($query, $value)
    {
        switch ($value){
            case 'daily':
                return $query->where('rent_type', 1);
                break;
            case 'monthly':
                return $query->where('rent_type', 2);
                break;
        }
    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateInBetween($query, $from, $to)
    {
        $from = Carbon::parse($from)->startOfDay()->toDateTimeString();
        $to = Carbon::parse($to)->endOfDay()->toDateTimeString();

        return $query->whereBetween('date_in', [$from, $to]);
    }

    public function scopeCreationDateBetween($query, $from, $to)
    {
        $from = Carbon::parse($from)->startOfDay()->toDateTimeString();
        $to = Carbon::parse($to)->endOfDay()->toDateTimeString();

        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCustomerName($query, $value)
    {
        return $query->whereHas('customer' , function($customer) use($value){
            $customer->where('name' , 'LIKE' , "%$value%") ;
        });
    }

    /**
     * Scope a query to get By Customer Id Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUintId($query, $value)
    {
        return $query->whereHas('unit', function ($unit) use($value) {
            $unit->where('unit_id', '=', $value);
        });
    }

    /**
     * Scope a query to get By Created At.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreatedAt($query, $value)
    {
        return $query->where('created_at' , 'LIKE' , "%$value%");
    }

    /**
     * Scope a query to get By Created At.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateTimeBetween($query, $dateFrom, $dateTo)
    {
        return $query
            ->whereNotNull('date_in_time')
            ->whereNotNull('date_out_time')
            ->when($dateFrom != 'null' , function ($query) use($dateFrom) {
                $query->where('date_in_time', '>=' , $dateFrom);
            })
            ->when($dateTo != 'null' , function ($query) use($dateTo) {
                $query->where('date_out_time', '<=' , $dateTo);
            });

    }

    public function scopeDateBetween($query, $dateFrom, $dateTo)
    {
        return $query
            ->whereNotNull('date_in')
            ->whereNotNull('date_out')
            ->when($dateFrom != 'null' , function ($query) use($dateFrom) {
                $query->where('date_in', '>=' , $dateFrom);
            })
            ->when($dateTo != 'null' , function ($query) use($dateTo) {
                $query->where('date_out', '<=' , $dateTo);
            });

    }

    public function scopeWhereDateIn($query , $dateFrom){

        return $query
            ->whereNotNull('date_in')
            ->when($dateFrom != 'null' , function ($query) use($dateFrom) {
                $query->where('date_in', '>=' , $dateFrom);
            });

    }

    public function scopeWhereIntersectsDateIn($query,$dateFrom){

       return  $query->whereNotNull('date_in')
                        ->where('date_in' , '>=' , $dateFrom)
                        ->orWhere('date_out' , '>' , $dateFrom);
    }

    public function scopeWhereIntersectsDateOut($query,$dateTo){

        return  $query->whereNotNull('date_out')
                        ->where('date_out' , '<' , $dateTo)
                        ->orWhere('date_in' , '<=' , $dateTo);
    }

    public function scopeWhereIntersectsDateOutCheck($query,$dateFrom,$dateTo){

        return  $query->whereNotNull('date_out')
                        ->where('date_out' , '>' , $dateFrom)
                        ->where('date_out' , '<=' , $dateTo);
    }



    public function scopeBySource($query , $source_id){

        return $query->where('source_id' , $source_id) ;
    }

    public function scopeByDateRange($query , $value){

        if(!is_array($value)){
            $value = func_get_args();
            array_shift($value);
        }

        $from = Carbon::parse($value[0])->startOfDay()->toDateString();
        $to = Carbon::parse($value[1])->endOfDay()->toDateString();
        return $query->whereDate('date_in', '>=' , $from)->whereDate('date_out', '<=' , $to);
    }

    public function scopeByDateFrom($query, $value)
    {
        $from = Carbon::parse($value)->format('Y-m-d H:i');
        return $query->where('date_in', '>=' , $from);
    }
    public function scopeByDateTo($query, $value)
    {
        $to = Carbon::parse($value)->format('Y-m-d H:i');
        return $query->where('date_in', '<=' , $to);
    }


    public function scopeByCreatedFrom($query, $value)
    {
        $from = Carbon::parse($value)->format('Y-m-d');
        $from = $from . ' 00:00:00';
        return $query->where('created_at', '>=' , $from);
    }
    public function scopeByCreatedTo($query, $value)
    {
        $to = Carbon::parse($value)->format('Y-m-d');
        $to = $to . ' 23:59:59';
        return $query->where('created_at', '<=' , $to);
    }

    /**
     * all user actions on wallets will be in this method
     *
     * @return MorphMany
     */
    public function transactions(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')->where('is_public',true)->orderByDesc('created_at');
    }


    public function notPublicTransactions(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')->where('is_public',false)->whereNull('deleted_at')->where('transaction_flag', 'normal')->whereJsonDoesntContain('meta->category' , 'service')->orderByDesc('created_at');
    }

    public function promisspryTransactions(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')->where('is_public',true)->where('is_promissory' , 1)->orderByDesc('created_at');
    }


    public function depositInsuranceTransactions(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')
            ->where('type' , 'deposit')
            ->where('is_public',true)->where('is_insurance' , 1)
            ->where('confirmed' , 0)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');
    }

    public function withdrawInsuranceTransactions(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')
            ->where('type' , 'withdraw')
            ->where('is_public',true)->where('is_insurance' , 1)
            ->where('confirmed' , 0)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at');
    }


    /**
     * This is consumed  by ClearReservationRelatedData Listener
     *
     * @return MorphMany
     */
    public function transactionsToDelete(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')->orderByDesc('created_at');
    }
    /**
     * all user actions on wallets will be in this method
     *
     * @return MorphMany
     */
    public function services(): MorphMany
    {
        return ($this instanceof \App\Wallet ? $this->holder : $this)
            ->morphMany(config('wallet.transaction.model'), 'payable')->where('meta->category', 'service')->whereNull('deleted_at')->orderBy('created_at' , 'desc');
    }

    public function team(){
         return $this->belongsTo(Team::class , 'team_id')->withTrashed() ;
    }

    public function source(){
         return $this->belongsTo(Source::class) ;
    }

    public static function countForDate(Carbon $carbon)
    {
        return static::where('date_in', '=', $carbon->startOfDay()->toDateTimeString())
            ->where('team_id', '=', auth()->user()->current_team_id)
            ->where('status', '=', self::STATUS_CONFIRMED)
            ->whereNull('checked_out')
            ->orWhere('date_out', '!= and <', $carbon->startOfDay()->toDateTimeString())
            ->count();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getNightsAttribute()
    {
        $date_in = new \DateTime($this->date_in);
        $date_out = new \DateTime($this->date_out);
        return $date_out->diff($date_in)->days ;
    }


    public function getCustomerNameAttribute()
    {
        return $this->customer ? $this->customer->name : null;
    }


    public function getNights(){
        $date_in = new \DateTime($this->date_in);
        $date_out = new \DateTime($this->date_out);
        return $date_out->diff($date_in)->days ;
    }
    /**
     * @return mixed
     * @throws \Exception
     */
    public function getDurationAttribute()
    {
        $day    = null;
        $hour   = null;
        $mintue = null;

        $checked_in = $this->checked_in;
        $checked_out = $this->checked_out;

        if (is_string($checked_in))
            $checked_in  = new \DateTime($checked_in);

        if (is_string($checked_out))
            $checked_out = new \DateTime($checked_out);

        if (!is_null($checked_out)) {
            $duration = $checked_in->diff($checked_out);
            $day    = $duration->format('%a');
            $hour   = $duration->format('%h');
            $mintue = $duration->format('%i');
        }

        return $day.' '.__('Day').' '. $hour.' '.__('Hour').' '.$mintue.' '.__('Minute');
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getOverOutNightsAttribute()
    {
        $date_in = new \DateTime();
        $date_out = new \DateTime($this->date_out);
        return $date_out->diff($date_in)->days ;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
//    public function getGlobalCheckInAttribute()
//    {
//        return SettingRegister::getSettingItem('day_start')->getValue(); ;
//    }

    /**
     * @return mixed
     * @throws \Exception
     */
//    public function getGlobalCheckOutAttribute()
//    {
//        return SettingRegister::getSettingItem('day_end')->getValue(); ;
//    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getPriceAttribute()
    {
        return [
            'price' => $this->sub_total,
            'total_price' => $this->total_price,
            'total_price_raw' => $this->total_price,
            'total_ewa' => $this->ewa_total,
            'total_vat' => $this->vat_total,
        ];
    }

    /**
     * @var Logs Activity update Description For Event.
     *
     */
    public function getDescriptionForEvent(string $eventName): string
    {

        if($eventName === 'created'){
            // new reservation addeed
            $message = "  انشاء الحجز رقم  {$this->number}";
        }

        if($eventName === 'updated'){

                if($this->occ){
                    $message = "  فتح العقد المغلق للحجز رقم {$this->number}";
                }
                elseif(is_null($this->status))
                {
                    $message = "  تغيير حالة الحجز رقم {$this->number} الى مؤكد";
                }
                elseif($this->action_type == self::ACTION_CANCELED && is_null($this->deleted_at))
                {
                    $message = " تغيير حالة الحجز رقم {$this->number} الى ملغى";
                }

                elseif($this->action_type == self::ACTION_RESERVATION_TIMEOUT && is_null($this->deleted_at))
                {
                    $message = " تغيير حالة الحجز رقم {$this->number} الى انتهت مهلة الدفع";
                }

                elseif($this->action_type == self::ACTION_RESERVATION_AWAITING_PAYMENT && is_null($this->deleted_at))
                {
                    $message = " تغيير حالة الحجز رقم {$this->number} الى بانتظار الدفع";
                }
                elseif($this->action_type == self::ACTION_RESERVATION_AWAITING_CONFIRMATION && is_null($this->deleted_at))
                {
                    $message = " حجز جديد رقم {$this->number}  بانتظار التأكيد";
                }

                elseif($this->action_type == self::ACTION_CHECKEDIN)
                {
                    $message = "  تسجيل دخول الحجز رقم {$this->number} ";
                }
                elseif($this->action_type == self::ACTION_CHECKEDOUT)
                {
                    $message = "  تسجيل خروج الحجز رقم {$this->number} ";
                }
                elseif($this->action_type == self::ACTION_UPDATERESERVATION)
                {
                    $message = "  تعديل الحجز رقم {$this->number}";
                }
                elseif($this->action_type == self::ACTION_UPDATERESERVATIONFROMCOMMAND)
                {
                    $message = "  تجديد تلقائى للحجز رقم {$this->number}";
                }else{
                    $message = "";
                }



        }

        if($eventName === 'deleted'){
            $message = "  حذف الحجز رقم  {$this->number}";
        }
        return $message;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function scopeUnitAvailableInBetween($query, $from, $to)
    {
        return $query->whereBetween('date_in', [$from, $to]);
    }


    /** -------------------------------------------- Calculations were appended ------------------------------------------------------- */

    public function getWithdrawSum(){
        $decimals_places = $this->wallet->decimal_places;
        return  abs($this->transactions->where('type', '=', 'withdraw')->where('is_public' , 1)->where('is_insurance' , 0)->sum('amount')/ ($decimals_places == 3 ? 1000 : 100));
    }

    public function getDepositSum(){
        $decimals_places = $this->wallet->decimal_places;
        return  abs($this->transactions->where('type', '=', 'deposit')->where('is_public' , 1)->where('is_insurance' , 0)->sum('amount')/ ($decimals_places == 3 ? 1000 : 100));
    }

    public function getServicesSum(){

        $services = $this->services;
        $sum = 0;

        foreach ($services as $service) {
            $sum += isset($service->meta['total_with_taxes']) ? $service->meta['total_with_taxes'] : 0;
        }
        return abs($sum);
    }

    public function getServicesWithoutTaxesSum(){
        $services = $this->services;
        $sum = 0;
        if(count($services)){
            foreach ($services as $service) {
                $sum += isset($service->meta['sub_total']) ? $service->meta['sub_total'] : 0;
            }
        }
        return abs($sum);
    }

    public function getServicesTaxesSum(){
        $services = $this->services;
        $sum = 0;
        if($services){
            foreach ($services as $service) {
                $ttx = isset($service->meta['ttx_total']) ? floatval($service->meta['ttx_total']) : 0;
                $vat = isset($service->meta['vat_total']) ? floatval($service->meta['vat_total']) : 0;
                $sum +=  $ttx + $vat ;
            }
        }

        return abs($sum);

    }

    public function getTtxTotalSum(){
        return $this->services()->sum('meta->ttx_total');
    }

    public function getVatTotalSum(){
        return $this->services()->sum('meta->vat_total');
    }



    /** -------------------------------------------- End Calculations ------------------------------------------------------- */


    public function rating()
    {
        return $this->hasOne(Rating::class, 'id', 'rating_id');
    }

    public function ratingTask()
    {
        return $this->morphOne(Task::class, 'taskable');
    }

    public function scopeByCreator($query , $created_by){
        return $query->where('created_by', '=' , $created_by) ;
    }

    public function cancel()
    {
        if ($this->checked_in){
            $this->checked_out = date('Y-m-d H:i:s');
        }

        $this->status = Reservation::STATUS_CANCELED;
        if ($this->save()) {
            if($this->is_online){
                event(new ReservationCanceled($this,false));
            }else{
                event(new ReservationCanceled($this));
            }
        }
    }


    public function cancelBill()
    {
        if (empty($this->bill_ref_id)) {
            Log::warning('cancelBill: bill_ref_id is empty. Request skipped.');
            return;
        }

        $client = new Client(['base_uri' => config('app.bills_url')]);
        $params = [
            'query' => [
                'application_id' => $this->team->sure_bills_client_id,
                'application_secret' => $this->team->sure_bills_secret,
            ]
        ];
        $client->put('/api/v1/bills/'.$this->bill_ref_id . '/cancel', $params);
    }

    public function timeoutBill(){

        if (empty($this->bill_ref_id)) {
            Log::warning('timeoutBill: bill_ref_id is empty. Request skipped.');
            return;
        }

        $client = new Client(['base_uri' => config('app.bills_url')]);
        $params = [
            'query' => [
                'application_id' => $this->team->sure_bills_client_id,
                'application_secret' => $this->team->sure_bills_secret,
            ]
        ];
        if($this->bill_ref_id){
            $client->put('/api/v1/bills/'.$this->bill_ref_id . '/timeout', $params);
        }
    }

    public function messagesLog()
    {
        return $this->hasMany(ReservationMessageLog::class)->orderByDesc('created_at');
    }

    public function invoices()
    {
        return $this->hasMany(ReservationInvoice::class)->whereNull('deleted_at')->orderByDesc('number');
    }

    public function pure_invoices_without_credit_notes()
    {
        return $this->hasMany(ReservationInvoice::class)->whereDoesntHave('invoiceCreditNote')->whereNull('deleted_at')->orderByDesc('number');
    }



    public function getHashIdAttribute()
    {
        return Hashids::connection('fandaqah')->encode($this->id);
    }

    public function attachedReservations()
    {
        return   Reservation::where('attachable_id' , $this->id)
                ->whereNull('deleted_at')
                ->where('status' , 'confirmed')
                ->whereNull('checked_out')
                ->get();
    }



    public function timeout(){

        if($this->status != 'confirmed'){
            $this->status = 'timeout';
            $this->action_type = self::ACTION_RESERVATION_TIMEOUT;
            $this->save();
        }

        // event(new ReservationCanceled($this,false));
    }

    public function logs(){
        return Activity::with('causer')->withoutGlobalScopes()->where('subject_type' , 'App\Reservation')->where('subject_id' , $this->id)->get();
    }

    public function getDepositTransactions(){
        return $this->transactions->where('type', '=', 'deposit')->where('is_public' , 1);
    }

    public function getWithdrawTransactions(){
        return $this->transactions->where('type', '=', 'withdraw')->where('is_public' , 1);
    }

    public function scopeAwaitingPayment($query)
    {
        return $query->where('status' , 'awaiting-payment');
    }

    public function scopeAwaitingPaymentForSureBills($query)
    {
        return $query->where('status' , 'awaiting-payment')
        ->whereNull('deleted_at')
        ->where('is_online',1)
        ->where('created_at', 'LIKE', '%' . Carbon::today()->format('Y-m-d'). '%')
        ->whereHas('team', function($t){
            $t->where('payment_preprocessor','sure-bills');
        });
    }

    public function confirmOnline()
    {
        // $unitHasReservation = checkIfUnitHasReservation($this->unit_id, $this->date_in);
        $unitHasReservation = Reservation::whereRaw('? between date_in and date_out', [Carbon::parse($this->date_in)->format('Y-m-d')])
            ->where('date_out', '!=', Carbon::parse($this->date_in)->format('Y-m-d'))
            ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->where('status' , 'confirmed')
            ->where('unit_id' , $this->unit_id)
            ->exists();

        if (!$unitHasReservation) {

            $reservation = Reservation::where('id' , $this->id)->first();
            $res_number = $reservation->number;
            $team_id = $reservation->team_id;


            if ($res_number == null || $res_number == '') {

                // Retrieve counter
                $counter = DB::table('team_counters')->where('team_id', $team_id)->first();

                // If counter does not exist, create it
                if (!$counter) {
                    DB::table('team_counters')->insert([
                        'team_id' => $team_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Retrieve the newly inserted counter
                    $counter = DB::table('team_counters')->where('team_id', $team_id)->first();
                }

                // Assign reservation number

                $this->number = $counter->last_reservation_number + 1;

                // Update last reservation number
                DB::table('team_counters')
                    ->where('team_id', $team_id)
                    ->update([
                        'last_reservation_number' => $counter->last_reservation_number + 1,
                        'updated_at' => now(),
                    ]);
            }






            $this->status = 'confirmed';
            $this->save();

            return [ 'status' => 'converted_to_confirmed_reservation' ];
        }

        return [ 'status' => 'unit_is_not_available' ];
    }


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function groupReservationBalanceMapper()
    {
        return $this->hasOne(GroupReservationBalanceMapper::class , 'reservation_id');
    }


    public function group_reservation()
    {
        return $this->belongsTo(GroupReservation::class);
    }

    public function reservationFreeServices(){
        return $this->hasMany(ReservationServiceMapper::class);
    }

    public function scopeBySourceNumber($query , $source_id){
        return $query->where('source_num' , $source_id) ;
    }

    public function updateReservationPriceByDay(Array $days) {
        $reservation_days = $this->prices['days'];
        $sub_total_old = 0;
        $total_price = 0;
        $sub_total = 0;
        $ewa_total = 0;
        $vat_total = 0;

        foreach ($reservation_days as $key => $day) {
            $sub_total_old += $day['price'];
        }

        foreach ($days as $day) {
            foreach ($reservation_days as $index => $reservation_day) {
                if($reservation_day['date'] === $day->date) {
                    $reservation_days[$index]['price'] = $day->mutatedValue;
                    $reservation_days[$index]['price_row'] = $day->mutatedValue;
                }
            }
        }

        foreach ($reservation_days as $key => $day) {
            $sub_total += $day['price'];
        }

        $sub_total_old = $this->getSubTotalOldPrices($reservation_days);

        $ewa_percentage = Setting::where('team_id', auth()->user()->current_team_id)
                            ->where('key', 'accommodation_tax')->first()->value;

        $vat_percentage = Setting::where('team_id', auth()->user()->current_team_id)
                            ->where('key', 'tax')->first()->value;

        $ewa_total = $sub_total / 100 * floatval($ewa_percentage);

        $vat_total = ($sub_total + $ewa_total) / 100 * floatval($vat_percentage);

        $total_price = $sub_total + $ewa_total + $vat_total;

        $change_rate_value = $sub_total - $sub_total_old;

        $change_rate_percentage = $change_rate_value * 100 / $sub_total_old;

        $presist_price = $this->prices;

        $presist_price['days'] = $reservation_days;

        $this->prices = $presist_price;

        $this->total_price = $total_price;

        $this->sub_total = $sub_total;

        $this->ewa_total = $ewa_total;

        $this->vat_total = $vat_total;

        $this->change_rate = $change_rate_percentage;

        //reflect values to price json

        $presist_price['price'] = $this->sub_total;

        $presist_price['sub_total'] = $this->sub_total;

        $presist_price['total_ewa'] = $this->ewa_total;

        $presist_price['total_vat'] = $this->vat_total;

        $presist_price['total_price'] = $this->total_price;

        $presist_price['total_price_raw'] = $this->total_price;

        $presist_price['ewa_parentage'] = $ewa_percentage;

        $presist_price['vat_parentage'] = $vat_percentage;

        $this->prices = $presist_price;

    }

    public static function checkNightRunExecuted(Reservation $reservation, $date) {
        // $currentDateTime = new \DateTime('now', new \DateTimeZone('Asia/Riyadh'));
        // $currentDateTimeCopy = clone $currentDateTime;
        // $currentDateTimeCopy->modify('-1 day');
        // $currentDateTimeCopy->setTime(0,0,0);
        // $dateTime = new \DateTime($date);
        // $dateTime->setTime(0,0,0);

        // // $nightRunTime = Setting::where('team_id', $reservation->team_id)
        // //                         ->where('key', 'night_run_time')
        // //                         ->latest()
        // //                         ->first();
        // if($reservation->checking_in == 0 && ($currentDateTime->format('Y-m-d') > $dateTime->format('Y-m-d'))) {

        //     if($dateTime->format('Y-m-d H:i:s') === $currentDateTimeCopy->format('Y-m-d H:i:s')) {
        //         if($currentDateTime->format('H:i') > '10:00') { //
        //             return true;
        //         }
        //         return false;
        //     } //if current date minus (1 day) == targetdate then check if the current date time greater then night run time
        //     return true; //executed
        // }

        $business_freeze_day = DB::table('settings')
                ->where('team_id', auth()->user()->current_team->id)
                ->where('key', 'business_day_freeze_date')
                ->first();
        if($business_freeze_day) {
            $business_day = $business_freeze_day->value;
            $business_day = Carbon::parse($business_day)->format('Y-m-d');
            $dateTime = Carbon::parse($date)->format('Y-m-d');
            if($dateTime < $business_day) {

                return true;
            }else {

                return false;
            }
        }

        return false; // not executed
    }

    /*
    * To calculate old sub total
    * old_prices -> prices -> day -> $targettedDay
    */
    private function getSubTotalOldPrices ($reservation_days) {
        $sub_total = 0;
        foreach ($reservation_days as $key => $value) {
            $current_day    = CheckDatesLang::getEnglishDay($value['date_name']) . '_day_price';
            $sub_total      += floatval($this->old_prices['prices']['day'][$current_day]);
        }
        return $sub_total;
    }

    public static function getReservationOfficialSignature($reservation_team_id, $reservation_id) {
        $signature = DigitalSignature::where('team_id',$reservation_team_id)
         ->where('ref_id',$reservation_id)
         ->where('type',DigitalSignature::TYPE_RESERVATION_USER)
         ->whereNotNull('user_id')
         ->first();
         if(isset($signature)) {
             try {
                 $signature['user_id'] = $signature->user_id;
                 $signature['signature'] = gzuncompress(base64_decode($signature->signature_base64));
                 $signature = (object) $signature;
             } catch (\Exception $e) {
                 \Log::error('Error decoding official digital signature: ' . $e->getMessage() . ' for team ' . $reservation_team_id . ' and reservation id ' . $reservation_id);
                 $signature = null;
             }
         }
         return $signature;
     }


    public function signedContracts()
    {
        return $this->hasMany(ReservationContract::class)->where('status', 'signed')->orderByDesc('signed_at');
    }


    public function signedContract()
    {
        return $this->hasOne(ReservationContract::class)
                    ->where('status', 'signed')
                    ->latest();
    }


}
