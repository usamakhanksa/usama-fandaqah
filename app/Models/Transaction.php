<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;
use Watson\Rememberable\Rememberable;
use App\Scopes\TeamScope;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use Rememberable, LogsActivity, SoftDeletes;

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'team_id',
        'unit_category_id',
        'type',
        'transaction_flag',
        'is_insurance',
        'amount',
        'amount_without_tax',
        'enable_tax_on_withdraw',
        'tax_percentage',
        'tax_amount',
        'supplier_tax_number',
        'invoice_number',
        'is_public',
        'is_promissory',
        'is_attached_to_invoice',
        'created_by',
        'updated_by',
        'kind',
        'description',
        'number',
        'confirmed',
        'meta',
        'receiver_bank_id',
        'bill_payload',
        'uuid',
        'correction_of_transaction_id',
        'correction_reason',
        'reversed_at',
        'reversal_transaction_id',
        'is_advance_deposit',
        'is_freezed',
        'cashier_shift_id',
        'zatca_status',
        'zatca_invoice_id',
        'zatca_uuid',
        'zatca_qr_code',
        'zatca_response',
        'zatca_submitted_at',
        'zatca_accepted_at',
        'vat_calculation_basis',
        'vat_category',
        'tourism_tax_amount',
        'accommodation_tax_amount',
        'business_date'
    ];

    protected $appends = ['payment_method', 'hash_id', 'service_log_number'];

    protected static $logAttributes = ['amount', 'type', 'meta', 'updated_by', 'unit_category_id'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $with = ['service_log'];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_without_tax' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'confirmed' => 'bool',
        'meta' => 'json',
        'bill_payload' => 'json',
        'is_insurance' => 'boolean',
        'is_promissory' => 'boolean',
        'is_attached_to_invoice' => 'boolean',
        'is_advance_deposit' => 'boolean',
        'is_freezed' => 'boolean',
        'tourism_tax_amount' => 'decimal:2',
        'accommodation_tax_amount' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        // auto-sets values on creation
        static::creating(function ($query) {
            if(auth()->check() && empty($query->created_by)){
                $query->created_by = auth()->user()->id;
                $query->updated_by = auth()->user()->id;
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
                $term->deleteable == 0 ? $query->transaction_flag = 'managerial' : $query->transaction_flag = 'normal';
            }
        });

        static::updating(function($query){
            if(auth()->check()) {
                $query->updated_by = auth()->user()->id;
            }
        });

        static::addGlobalScope(new TeamScope());
    }

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

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model'));
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'payable_id');
    }

    public function scopeByCreator($query, $value)
    {
        return $query->whereHas('creator', function($creator) use($value){
            $creator->where('id', $value);
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
            $reservation->whereHas('customer', function($customer) use($value){
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
        return $this->hasOne(ServiceLog::class, 'transaction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'reversal_transaction_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'correction_of_transaction_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getServiceLogNumberAttribute()
    {
        return $this->service_log ? $this->service_log->number : null;
    }

    public function getPaymentMethodAttribute()
    {
        if(is_array($this->meta) && array_key_exists('payment_type', $this->meta)){
            return __(ucfirst($this->meta['payment_type']));
        } else {
            return __('Nothing');
        }
    }

    public function getTeamIdAttribute()
    {
        if ($this->payable instanceof Team) {
            return $this->payable->id;
        }

        return $this->payable->team_id;
    }

    public function scopeWhereCreatedCustom($query, $from, $to)
    {
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

    public function termIsTransaferFromManagementToSafe(){
        return Term::where('id', $this->meta['type'])
            ->where('name->ar', 'تحويل من الادارة الى الصندوق')
            ->first();
    }

    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }
}