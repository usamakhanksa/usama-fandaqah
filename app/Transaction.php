<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Watson\Rememberable\Rememberable;
use Illuminate\Support\Facades\DB;
use app\Team;
use function config;

class Transaction extends Model
{
    use Rememberable;
    use LogsActivity , SoftDeletes;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var array
     */
    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'team_id',
        'is_freezed',
        'uuid',
        'type',
        'transaction_flag',
        'amount',
        'is_public',
        'created_by',
        'updated_by',
        'confirmed',
        'meta',
        'bill_payload',
        'unit_category_id'
    ];
    protected $appends = ['payment_method' , 'hash_id' , 'service_log_number'] ;

    protected static $logAttributes = ['amount', 'type', 'meta' , 'updated_by','unit_category_id'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $with = ['service_log'];
    /**
     * @var array
     */
    protected $casts = [
        'amount' => 'int',
        'confirmed' => 'bool',
        'meta' => 'json',
        'bill_payload' => 'json'
    ];


    protected static function boot()
    {
        parent::boot();
        // auto-sets values on creation
        //

        static::creating(function ($query) {
            if(\Auth::check() and empty($query->created_by)){
                $query->created_by = auth()->user()->id;
                $query->updated_by =  auth()->user()->id;
            }

            /**
             * @todo : don't forget testing in many different cases
             * @author : emad rashad
             * auto fill transaction flag (transaction_flag) for the new two terms from management to the safe ( used in safe movement report )
             * this will be helpful in calculating total amount transferred from the safe to management and opposite
             */
            // Check first for the key isset
            if(isset($query->meta['type'])){
                $term = Term::find($query->meta['type']);
                $term->deleteable == 0 ? $query->transaction_flag = 'managerial' : $query->transaction_flag = 'normal' ;
            }
        });

        static::updating(function($query){
            if(\Auth::check())
            $query->updated_by =  auth()->user()->id;

        });

    }


    /**
     * @return string
     * @var Logs Activity update Description For Event.
     *
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $number = $this->number ?? null;
        $reservation_number = $this->reservation ? $this->reservation->number : '';
        $trans_event_name = __($eventName);
        if($this->payable_type == 'App\Team'){
            if($eventName == 'created'){
                if($this->type == 'deposit'){
                    return "انشاء سند قبض رقم {$this->number} من الادارة المالية";
                }else{
                    return "انشاء سند صرف رقم {$this->number} من الادارة المالية";
                }
            }elseif ($eventName == 'updated') {
                if($this->type == 'deposit'){
                    return "تعديل سند قبض رقم {$this->number} من الادارة المالية";
                }else{
                    return "تعديل سند صرف رقم {$this->number} من الادارة المالية";
                }

            }else{
                if($this->type == 'deposit'){
                    return "حذف سند قبض رقم {$this->number} من الادارة المالية";
                }else{
                    return "حذف سند صرف رقم {$this->number} من الادارة المالية";
                }

            }
        }else{
            if($this->type == "deposit"){
                $type = __('Cash Receipt');
            }elseif ($this->type == "withdraw" ) {
                if($number == null && $this->meta['category'] == 'reservation'){
                    return "{$trans_event_name} سجل مالي للحجز رقم {$reservation_number}";
                }
                $type = ($this->meta['category'] == 'service') ?  __('Service Transaction') : __('Payment Voucher');
            }
            $number_word = ($this->number) ? 'رقم' : '';
            $reservation_number_word = $this->reservation ? 'للحجز رقم' : '-' ;
            return "{$trans_event_name} {$type} {$number_word} {$number} {$reservation_number_word} {$reservation_number}";
        }

    }


    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.transaction.table');
        }
        return parent::getTable();
    }

    /**
     * @return MorphTo
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model'));
    }


    public function reservation(){

//        return $this->belongsTo(Reservation::class , 'payable_id')->withoutGlobalScopes();
        return $this->belongsTo(Reservation::class , 'payable_id');
    }


    public function scopeByCreator($query, $value)
    {
        return $query->whereHas('creator' , function($creator) use($value){
            $creator->where('id' , $value);
        });
    }

    public function scopeByReservationNumber($query, $value)
    {
        return $query->whereHas('reservation', function ($reservation) use($value) {
            $reservation->where('number', "=", $value);

        });
    }

    public function scopeByCustomerIdNumber($query, $value)
    {
        return $query->whereHas('reservation', function ($reservation) use($value) {
            $reservation->whereHas('customer' , function($customer) use($value){
                $customer->where('id_number', "LIKE", '%' . $value . '%');
            });
        });
    }

    public function scopeByTransactionType($query, $value)
    {
        return $query->whereJsonContains('meta->payment_type', $value);
    }

    public function scopeByStatement($query, $value)
    {
        return $query->where('meta->statement', 'like', '%' . $value . '%');
    }

    public function scopeByNumber($query, $value)
    {
        return $query->whereNumber($value);
    }

    public function scopeByUnitNumber($query, $value)
    {
        return $query->whereHas('reservation', function ($query) use($value) {
            $query->where('unit_id', '=', $value);
        });
    }

    public  static function getServiceLogs($ids)
    {
        return ServiceLog::whereIn('transaction_id' , $ids)->orderBy('number' , 'desc')->get();
    }

    public function scopeByDateRange($query, $value)
    {
        if(!is_array($value)){
            $value = func_get_args();
            array_shift($value);
        }

        if (isset($value[0])) {
            $from = Carbon::parse($value[0])->startOfDay()->toDateString();
            $query->whereDate('meta->date', '>=' , $from);
        }

        if (isset($value[1])) {
            $to = Carbon::parse($value[1])->endOfDay()->toDateString();
            $query->whereDate('meta->date', '<=' , $to);
        }
        return $query;
    }

    public function service_log()
    {
        return $this->hasOne(ServiceLog::class , 'transaction_id');
    }

    /**
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function getServiceLogNumberAttribute()
    {
       return  $this->service_log ? $this->service_log->number : null;
    }
    public function getPaymentMethodAttribute()
    {
        if(is_array($this->meta) and array_key_exists('payment_type' , $this->meta)){
            return __(ucfirst($this->meta['payment_type']));
        }else{
            return  __('Nothing')  ;
        }
    }

    public function getTeamIdAttribute()
    {
        if ($this->payable instanceof Team) {
            return $this->payable->id;
        }

        return $this->payable->team_id;
    }


    public function scopeWhereCreatedCustom($query,$from,$to){

        return $query->whereBetween(DB::raw('DATE(created_at)'), array($from, $to));
    }

    public static function scthTypes()
    {
        return [
            0 => 'Not Applicable',
            1  => 'Other',
            2  => 'Laundry',
            3   =>  'Wifi - Internet',
            4   =>  'Car Parking',
            5   =>  'Food',
            6   =>  'Food & Beverages',
            7   =>  'Beverages',
            8   =>  'Cold Drinks',
            9   =>  'Hot Drinks',
            10  =>  'Breakfast',
            11  =>  'Lunch',
            12  =>  'Dinner',
            13  =>  'Bakery & Cakes',
            14  =>  'Swimming pool',
            15  =>  'Gym',
            16  =>  'SPA & Beauty Services',
            17  =>  'Pick & Drop (Transport Services)',
            18  =>  'Minibar',
            19  =>  'Cable - TV',
            20  =>  'Extra Bed',
            21  =>  'Hairdresser',
            22  =>  'Shopping',
            23  =>  'Organized Tours Services',
            24  =>  'Tour Guide Services',
        ];
    }

    /**
     * This funciton to avoid calculating service general transaction with transfer from management to safe transaction
     * @return mixed
     */
    public function termIsTransaferFromManagementToSafe(){
        return Term::where('id', $this->meta['type'])->where('name->ar' , 'تحويل من الادارة الى الصندوق')->first();
    }

    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }
}
