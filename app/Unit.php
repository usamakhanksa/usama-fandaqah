<?php

namespace App;

use Exception;
use Carbon\Carbon;
use App\Reservation;
use NumberFormatter;
use App\Traits\HasTeam;
use Carbon\CarbonPeriod;
use App\Traits\LogsActivity;
use Spatie\MediaLibrary\Models\Media;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Surelab\Settings\ValueObjects\SettingRegister;
use App\Scopes\TeamScope;

class Unit extends Model implements HasMedia
{

    use Rememberable;
    use HasTranslations, LogsActivity, SoftDeletes;

    use HasMediaTrait {
        HasMediaTrait::addMedia as parentAddMedia;
    }

    public $translatable = ['name', 'description', 'short_description'];

//    protected $appends = ['reservations_count' , 'future_reservations_count'  , 'online_reservations_count' , 'reservations_date'] ;

    protected $primaryKey = 'id';
    protected $with = ['unit_category', 'maintenances'];
    protected static $logAttributes = ['unit_number', 'name' , 'status'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected $appends = ['maintenance_info'];
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;
    const STATUS_UNDER_CLEANING = 2;
    const STATUS_UNDER_MAINTENANCE = 3;

    const STATUS_AVAILABLE = 4;
    const STATUS_CHECKED_IN = 5;
    const STATUS_BOOKED = 6;
    const STATUS_AWAITING_CONFIRMATION = 7;

    protected $guarded = [];
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->width(130)
            ->height(130)
            ->extractVideoFrameAtSecond(15);
    }

    public function registerMediaCollections()
    {
        $this->addMediaCollection('main')->singleFile();
        $this->addMediaCollection('images');
    }

    public static $rules = [
        'unit_number' => 'required|unique:units,unit_number'
    ];

    /**
     * [rules description]
     * @return [type] [description]
     */
    public function rules()
    {
        return [
            'unit_number' => 'required|unique:units',
        ];
    }

    public function maintenances()
    {
        return $this->hasMany(UnitMaintenance::class, 'unit_id', 'id');
    }

    public function cleanings()
    {
        return $this->hasMany(UnitCleaning::class, 'unit_id', 'id');
    }

    /**
     * [reservations description]
     * @return [type] [description]
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'unit_id', 'id')
            ->with('company')
            ->whereIn('status' , ['confirmed','awaiting-payment'])
            ->whereNull('checked_out')
            ->whereNull('deleted_at')
            ->where('team_id', '!=',null);
    }

    public function onlineReservations()
    {
        return $this->hasMany(OnlineReservation::class, 'unit_id', 'id')
            ->where('team_id', '!=',null);
    }

    public function blockedDays()
    {
        $days = [];

        /** @var Reservation $reservation */
        foreach ($this->reservations as $reservation) {
            $start = Carbon::parse($reservation->date_in);
            $end = Carbon::parse($reservation->date_out);

            $period = CarbonPeriod::create($start, $end);

            /** @var Carbon $date */
            foreach ($period as $key => $date) {
                $days[] = $date->format('Y-m-d');
            }
        }
        $days = array_unique($days);

        if (($key = array_search($reservation->date_out, $days)) !== false) {
            unset($days[$key]);
        }
        return $days;
    }

    /**
     * [unit_category description]
     * @return [type] [description]
     */
    public function unit_category()
    {
        return $this->belongsTo(UnitCategory::class);
    }

    /**
     * [team description]
     * @return [type] [description]
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }


    /**
     * @todo this is an old version for update reservation rolled back as per islam rashad request
     * [getDatesFromRange description]
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    public function getDatesFromRange($start, $end, $rent_type='daily',$special_prices = [])
    {
        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        $total_price = 0;
        $min_sub_total = 0;
        if ($rent_type == 2) {
            $total_price = $this->monthPrice($period);
            $min_sub_total = $this->minMonthPrice($period);
        }else{
            foreach ($period as $date) {
                if (isset($special_prices[$date->format('Y-m-d')])) {
                    $day_price = $special_prices[$date->format('Y-m-d')];
                } else {
                    $day_price = $this->dayPrice($date->format('l'));
                }
                $min_day_price = $this->minDayPrice($date->format('l'));
                $total_price += $day_price;
                $min_sub_total += $min_day_price;
                $dates[] = [
                    'date' => $date->format('Y/m/d'),
                    'date_name' => __($date->format('l')),
                    'price_row' => $day_price,
                    'price' =>  $day_price
                ];
            }
        }

        $t = $total_price;
        $vat = $this->getVatTotal($total_price,false);
        $ewa = $this->getEwaTotal($total_price,false);
        $tourismTotal = $this->getTourismTaxTotal($total_price,false);

        $total_raw = $total_price + $vat + $ewa + $tourismTotal ;

        $totals = [
            'currency' => 'SAR',
            'days' => $dates,
            'price' => $total_price,
            'vat_parentage' => $this->getVat(),
            'ewa_parentage' => $this->getEwa(),
            'sub_total' => $total_price,
            'min_sub_total' => $min_sub_total,
            'total_vat' => $vat,
            'total_ewa' => $ewa,
            'total_price' => number_format($total_raw,2),
            'total_price_raw' => sprintf("%.2f", $total_price + $vat + $ewa + $tourismTotal),
            // 'total_raw' => sprintf("%.10f", $total_price + $vat + $ewa + $tourismTotal),
            'total_tourism' => $tourismTotal ,
            'tourism_percentage' => $this->getTourismTax()
        ];

        return $totals;
    }

    /**
     * [getDatesFromRange description]
     * @param  [type] $start [description]
     * @param  [type] $end   [description]
     * @return [type]        [description]
     */
    // public function getDatesFromRange($start, $end, $rent_type='daily')
    // {
    //     if ($start != $end) {
    //         $end->subDay();
    //     }
    //     $period = CarbonPeriod::create($start, $end);
    //     $dates = [];
    //     $total_price = 0;
    //     $min_sub_total = 0;
    //     if ($rent_type == 2) {
    //         $total_price = $this->monthPrice($period);
    //         $min_sub_total = $this->minMonthPrice($period);
    //     }else{
    //         foreach ($period as $date) {
    //             $day_price = $this->dayPrice($date->format('l'));
    //             $min_day_price = $this->minDayPrice($date->format('l'));
    //             $total_price += $day_price;
    //             $min_sub_total += $min_day_price;
    //             $dates[] = [
    //                 'date' => $date->format('Y/m/d'),
    //                 'date_name' => __($date->format('l')),
    //                 'price_row' => $day_price,
    //                 'price' =>  $day_price
    //             ];
    //         }
    //     }

    //     $t = $total_price;
    //     $vat = (float) number_format($this->getVatTotal($total_price,false),5  , '.' , '');
    //     $ewa = (float) number_format($this->getEwaTotal($total_price,false),5  , '.' , '');
    //     $tourismTotal = (float) number_format($this->getTourismTaxTotal($total_price,false));

    //     $total_raw = $total_price + $vat + $ewa + $tourismTotal ;


    //     $totals = [
    //         'currency' => 'SAR',
    //         'days' => $dates,
    //         'price' => $total_price,
    //         'vat_parentage' => $this->getVat(),
    //         'ewa_parentage' => $this->getEwa(),
    //         'sub_total' => $total_price,
    //         'min_sub_total' => $min_sub_total,
    //         'total_vat' => $vat,
    //         'total_ewa' => $ewa,
    //         'total_price' => (float) number_format($total_raw,2  , '.' , ''),
    //         'total_price_raw' => (float) number_format($total_price + $vat + $ewa + $tourismTotal,2 , '.' , ''),
    //         // 'total_raw' => sprintf("%.10f", $total_price + $vat + $ewa + $tourismTotal),
    //         'total_tourism' => $tourismTotal ,
    //         'tourism_percentage' => $this->getTourismTax()
    //     ];

    //     return $totals;
    // }

    /**
      * @todo this is an old version for update reservation rolled back as per islam rashad request
     * @param $start
     * @param $end
     * @param array $prices
     * @param $rate
     * @param null $old_rent_type
     * @param string $rent_type
     * @return array
     */
    public function getDatesFromRangeWithOldPrices($start, $end, array $prices, $rate, $old_rent_type = null, $rent_type = 'daily')
    {
        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        $total_price = 0;
        $min_total_price = 0;
        if ($rent_type == 2) {
            $total_price = $this->monthOldPrice($prices['prices']['month'], $period);
            $min_day_price = $this->minMonthOldPrice($prices['min_prices']['month'], $period , $prices['min_prices']);
            if ($old_rent_type == 2)
                $total_price = ($total_price + ($total_price * $rate) /100);
        }else {
            foreach ($period as $date) {
                $day_price = $this->dayOldPrice($date->format('l'), $prices['prices']['day']);
                $min_day_price = $this->minDayOldPrice($date->format('l'), $prices['min_prices']['day']);
                if ($old_rent_type == 1)
                    $day_price = ($day_price + ($day_price * $rate) /100);
                $total_price += $day_price;
                $min_total_price += $min_day_price;
                $dates[] = [
                    'date' => $date->format('Y/m/d'),
                    'date_name' => __($date->format('l')),
                    'price_row' => $day_price,
                    'price' =>   $day_price
                ];
            }
        }
        $vat = 0;
        $ewa = 0;
        $ewa_formatted = '';
        $tourismTotal = 0;
        $tourismTotal_formatted = '';
        $ewaTax = 0;
        $vatTax = 0;
        $tourismTax = 0;

        if($prices['ewa_parentage']) {
            $ewaTax = $prices['ewa_parentage'];
            $ewaToPay = ($total_price / 100) * $ewaTax;
            $ewa = $ewaToPay;
            $ewa_formatted = number_format($ewaToPay, 2);
        }

        if($prices['vat_parentage']) {
            $vatTax = $prices['vat_parentage'];
            $vatToPay = (($total_price + $ewa) / 100) * $vatTax;
            $vat = $vatToPay;
            $vat_formatted = number_format($vat, 2);
        }

        if($prices['tourism_percentage']) {
            $tourismTax = $prices['tourism_percentage'];
            $tourismTaxToPay = ($total_price / 100) * $tourismTax;
            $tourismTotal = $tourismTaxToPay;
            $tourismTotal_formatted = number_format($tourismTaxToPay, 2);
        }

        $total_raw = floatval($total_price) + floatval($vat) + floatval($ewa) + floatval($tourismTotal) ;
//        $total = round($total_raw);
        $total_raw = sprintf("%.2f", $total_raw);
//        $total_raw = round($total_raw,1);

        $totals = [
            'currency' => 'SAR',
            'days' => $dates,
            'price' => $total_price,
            'vat_parentage' => $vatTax,
            'ewa_parentage' => $ewaTax,
            'sub_total' => $total_price,
            'min_sub_total' => $min_day_price,
            'total_vat' => $vat,
            'total_ewa' => $ewa,
            'total_price' => $total_raw,
            'total_price_raw' => $total_raw,
            'total_tourism' => $tourismTotal ,
            'tourism_percentage' => $tourismTax
        ];

        return $totals;
    }

    /**
     * @param $start
     * @param $end
     * @param array $prices
     * @param $rate
     * @param null $old_rent_type
     * @param string $rent_type
     * @return array
     */
    // public function getDatesFromRangeWithOldPrices($start, $end, array $prices, array $reservation_prices_days , $oStatus = 'create' ,  $rate, $old_rent_type = null, $rent_type = 'daily')
    // {

    //     if ($start != $end) {
    //         $end->subDay();
    //     }
    //     $period = CarbonPeriod::create($start, $end);
    //     $dates = [];
    //     $total_price = 0;
    //     $min_total_price = 0;
    //     if ($rent_type == 2) {
    //         $total_price = $this->monthOldPrice($prices['prices']['month'], $period);
    //         $min_day_price = $this->minMonthOldPrice($prices['min_prices']['month'], $period , $prices['min_prices']);
    //         if ($old_rent_type == 2)
    //             $total_price = ($total_price + ($total_price * $rate) /100);
    //     }else {

    //         foreach ($period as $date) {
    //             $day_price = $this->dayOldPrice($date->format('l'), $prices['prices']['day']);
    //             $min_day_price = $this->minDayOldPrice($date->format('l'), $prices['min_prices']['day']);
    //             if ($old_rent_type == 1){

    //                 if($oStatus == 'update'){

    //                     foreach($reservation_prices_days as $obj){
    //                         if(Carbon::parse($obj['date']) == $date){
    //                             $day_price = $obj['price'];
    //                         }
    //                     }
    //                 }else{
    //                     $day_price = ($day_price + ($day_price * $rate) /100);
    //                 }



    //             }




    //             $total_price += $day_price;
    //             $min_total_price += $min_day_price;
    //             $dates[] = [
    //                 'date' => $date->format('Y/m/d'),
    //                 'date_name' => __($date->format('l')),
    //                 'price_row' =>  $day_price ,
    //                 'price' =>   $day_price
    //             ];



    //         }

    //         // dd($dates);
    //     }
    //     $vat = 0;
    //     $ewa = 0;
    //     $ewa_formatted = '';
    //     $tourismTotal = 0;
    //     $tourismTotal_formatted = '';
    //     $ewaTax = 0;
    //     $vatTax = 0;
    //     $tourismTax = 0;

    //     if($prices['ewa_parentage']) {
    //         $ewaTax = $prices['ewa_parentage'];
    //         $ewaToPay = ($total_price / 100) * $ewaTax;
    //         $ewa = $ewaToPay;
    //         $ewa_formatted = (float) number_format($ewaToPay, 5 ,  '.' , '');
    //     }

    //     if($prices['vat_parentage']) {
    //         $vatTax = $prices['vat_parentage'];
    //         $vatToPay = (($total_price + $ewa) / 100) * $vatTax;
    //         $vat = $vatToPay;
    //         $vat_formatted = (float) number_format($vat, 5 ,  '.' , '');
    //     }

    //     if($prices['tourism_percentage']) {
    //         $tourismTax = $prices['tourism_percentage'];
    //         $tourismTaxToPay = ($total_price / 100) * $tourismTax;
    //         $tourismTotal = $tourismTaxToPay;
    //         $tourismTotal_formatted = (float) number_format($tourismTaxToPay, 5 ,  '.' , '');
    //     }
    //     $total_raw = $total_price + $vat + $ewa + $tourismTotal ;

    //     $totals = [
    //         'currency' => 'SAR',
    //         'days' => $dates,
    //         'price' => $total_price,
    //         'vat_parentage' => $vatTax,
    //         'ewa_parentage' => $ewaTax,
    //         'sub_total' => $total_price,
    //         'min_sub_total' => $min_total_price,
    //         'total_vat' => $vat,
    //         'total_ewa' => $ewa,
    //         'total_price' => (float) number_format($total_raw,2  , '.' , ''),
    //         'total_price_raw' => (float) number_format($total_raw,2  , '.' , ''),
    //         'total_tourism' => $tourismTotal ,
    //         'tourism_percentage' => $tourismTax
    //     ];

    //     return $totals;
    // }

    public function monthOldPrice($price, $period=null)
    {
        if(isset($period)){
            $days = count($period);
            $monthly_single_days = SettingRegister::getSettingItem('monthly_single_days')->getValue();
            if($monthly_single_days){
                $month_count =  floor($days/30);
                $total_price = round($price * $month_count, 2);
                foreach ($period as $key => $date) {
                    if($key > ($month_count*30)-1){
                        $day_price = $this->dayPrice($date->format('l'));
                        $total_price += $day_price;
                    }
                }
                return $total_price;
            }else{
                return round($price * $days/30, 2);
            }
        }
        return $price;
    }

    public function minMonthOldPrice($price, $period=null , $prices = null)
    {
        if(isset($period)){
            $days = count($period);
            $monthly_single_days = SettingRegister::getSettingItem('monthly_single_days')->getValue();
            if($monthly_single_days){
                $month_count =  floor($days/30);
                $total_price = round($price * $month_count, 2);

                foreach ($period as $key => $date) {
                    if($key > ($month_count*30)-1){
                        $day_price = $this->minDayOldPrice($date->format('l') , $prices['day']);
                        $total_price += $day_price;
                    }
                }
                return $total_price;
            }else{
                return round($price * $days/30, 2);
            }
        }
        return $price;
    }

    /**
     * @param null $period
     * @return false|float|mixed
     */
    public function monthPrice($period=null)
    {
        if(isset($period)){
            $days = count($period);
            $monthly_single_days = SettingRegister::getSettingItem('monthly_single_days')->getValue();
            if($monthly_single_days){
                $month_count =  floor($days/30);
                $total_price = round($this->month_price * $month_count, 5);
                foreach ($period as $key => $date) {
                    if($key > ($month_count*30)-1){
                        $day_price = $this->dayPrice($date->format('l'));
                        $total_price += $day_price;
                    }
                }
                return $total_price;
            }else{
                return round($this->month_price * $days/30, 5);
            }
        }
        return $this->month_price;
    }

    public function minMonthPrice($period=null)
    {
        if(isset($period)){
            $days = count($period);
            $monthly_single_days = SettingRegister::getSettingItem('monthly_single_days')->getValue();
            if($monthly_single_days){
                $month_count =  floor($days/30);
                $total_price = round($this->min_month_price * $month_count, 2);
                foreach ($period as $key => $date) {
                    if($key > ($month_count*30)-1){
                        $day_price = $this->minDayPrice($date->format('l'));
                        $total_price += $day_price;
                    }
                }
                return $total_price;
            }else{
                return round($this->min_month_price * $days/30, 2);
            }
        }
        return $this->month_price;
    }

    public function prices()
    {
        return [
            'month' => $this->monthPrice(),
            'day' => [
                'sunday_day_price'  =>  $this->sunday_day_price,
                'monday_day_price'  =>  $this->monday_day_price,
                'tuesday_day_price' =>  $this->tuesday_day_price,
                'wednesday_day_price'   =>  $this->wednesday_day_price,
                'thursday_day_price'    =>  $this->thursday_day_price,
                'friday_day_price'  =>  $this->friday_day_price,
                'saturday_day_price'    =>  $this->saturday_day_price,
                'month_price'   =>  $this->month_price,
            ],
        ];
    }

    public function minPrices()
    {
        return [
            'month' => $this->min_month_price,
            'day' => [
                'min_sunday_day_price'  =>  $this->min_sunday_day_price,
                'min_monday_day_price'  =>  $this->min_monday_day_price,
                'min_tuesday_day_price' =>  $this->min_tuesday_day_price,
                'min_wednesday_day_price'   =>  $this->min_wednesday_day_price,
                'min_thursday_day_price'    =>  $this->min_thursday_day_price,
                'min_friday_day_price'  =>  $this->min_friday_day_price,
                'min_saturday_day_price'    =>  $this->min_saturday_day_price,
                'min_month_price'   =>  $this->min_month_price,
            ],
        ];
    }

    /**
     * @param $day
     * @param array $prices
     * @return float|mixed
     */
    public function dayOldPrice($day, array $prices)
    {
        switch ($day) {
            case "Sunday":
                return $prices['sunday_day_price'];
                break;
            case "Monday":
                return $prices['monday_day_price'];
                break;
            case "Tuesday":
                return $prices['tuesday_day_price'];
                break;
            case "Wednesday":
                return $prices['wednesday_day_price'];
                break;
            case "Thursday":
                return $prices['thursday_day_price'];
                break;
            case "Friday":
                return $prices['friday_day_price'];
                break;
            case "Saturday":
                return $prices['saturday_day_price'];
                break;
            default:
                echo null;
        }
    }

    public function minDayOldPrice($day, array $prices)
    {
        switch ($day) {
            case "Sunday":
                return $prices['min_sunday_day_price'];
                break;
            case "Monday":
                return $prices['min_monday_day_price'];
                break;
            case "Tuesday":
                return $prices['min_tuesday_day_price'];
                break;
            case "Wednesday":
                return $prices['min_wednesday_day_price'];
                break;
            case "Thursday":
                return $prices['min_thursday_day_price'];
                break;
            case "Friday":
                return $prices['min_friday_day_price'];
                break;
            case "Saturday":
                return $prices['min_saturday_day_price'];
                break;
            default:
                echo null;
        }
    }

    public function minDayPrice($day)
    {
        switch ($day) {
            case "Sunday":
                return $this->min_sunday_day_price;
                break;
            case "Monday":
                return $this->min_monday_day_price;
                break;
            case "Tuesday":
                return $this->min_tuesday_day_price;
                break;
            case "Wednesday":
                return $this->min_wednesday_day_price;
                break;
            case "Thursday":
                return $this->min_thursday_day_price;
                break;
            case "Friday":
                return $this->min_friday_day_price;
                break;
            case "Saturday":
                return $this->min_saturday_day_price;
                break;
            default:
                echo null;
        }
    }

    public function dayPrice($day)
    {
        switch ($day) {
            case "Sunday":
                return $this->sunday_day_price;
                break;
            case "Monday":
                return $this->monday_day_price;
                break;
            case "Tuesday":
                return $this->tuesday_day_price;
                break;
            case "Wednesday":
                return $this->wednesday_day_price;
                break;
            case "Thursday":
                return $this->thursday_day_price;
                break;
            case "Friday":
                return $this->friday_day_price;
                break;
            case "Saturday":
                return $this->saturday_day_price;
                break;
            default:
                echo null;
        }
    }

    /**
     * Scope a query to get only Between Date Reservations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query, $day)
    {
        return $query->whereDoesntHave('reservations', function (Builder $query) use($day){
                $query->whereStartDateBetween($day)->whereNull('checked_out');
            })
            ->where('status', 1)->whereEnabled(true);
    }

    /**
     * Scope a query to get Under Cleaning.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderCleaning($query)
    {
        return $query->where('status', 2)->whereEnabled(true);
    }

    /**
     * Scope a query to get  UNDER_MAINTENANCE.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnderMaintenance($query)
    {
        return $query->where('status', 3)->whereEnabled(true);
    }

    /**
     * [getVat description]
     * @return [type] [description]
     */
    public function getVat()
    {
        $vatSetting = getVatPercentageForUnit($this->team_id);
        return $vatSetting ? (double)$vatSetting : 0;
    }

    /**
     * [getVatTotal description]
     * @param  [type] $total_price [description]
     * @return [type]              [description]
     */
    public function getVatTotal($total_price,$formatted = true)
    {
        $vat = $this->getVat();
        $vatToPay = (($total_price + $this->getEwaTotal($total_price,false)) / 100) * $vat;
        return $formatted ? number_format($vatToPay, 2) : $vatToPay;
    }

    /**
     * [getEwa description]
     * @return [type] [description]
     */
    public function getEwa()
    {
        $ewaSetting = getEwaPercentageForUnit($this->team_id);
        return $ewaSetting ? (double)$ewaSetting : 0;
    }

    /**
     * [getEwaTotal description]
     * @param  [type]  $total_price [description]
     * @param  boolean $formatted   [description]
     * @return [type]               [description]
     */
    public function getEwaTotal($total_price,$formatted = true)
    {
        $ewa = $this->getEwa();
        $ewaToPay = ($total_price / 100) * $ewa;
        return $formatted ? number_format($ewaToPay, 2) : $ewaToPay;
    }

    /**
     * [getReservations description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getOnlineReservations($day)
    {
        return $this->onlineReservations()->with('customer', 'unit')
            ->where('status', '=', 'waiting')
            ->where('date_out', '!=', $day)
            ->betweenDate($day)
            ->get();
    }

    public function getOnlineReservationsForIndex($day)
    {
        return $this->onlineReservations()
            ->where('status', '=', 'waiting')
            ->where('date_out', '!=', $day)
            ->betweenDate($day)
            ->get();
    }

    /**
     * [getReservations description]
     * @param  [type] $date [description]
     * @return [type]       [description]
     */
    public function getReservations($day)
    {
        return $this->reservations()
            ->betweenDate($day)
            ->whereNull('checked_out')
            ->where('date_out', '!=', $day)
            ->with(['customer', 'wallet', 'unit','invoices'])
            ->get();
    }


    /**
     * [has_reservation description]
     * @param  [type]  $date [description]
     * @return boolean       [description]
     */
    public function has_reservation($date)
    {
        return (bool) ($this->getReservationByDay($date));
    }

    /**
     * Check Intersections based on selected dates
     * @note needed for the new website and avoiding corneer crash
     *
     * @param [type] $start
     * @param [type] $end
     * @return boolean
     */
    public function has_intersection($start,$end){

        return (bool) ($this->getReservationIntersection($start,$end));
    }

    public function has_intersection_check($start,$end){

        return (bool) ($this->getReservationIntersectionCheck($start,$end));
    }
    /**
     * [getReservationsDateAttribute description]
     * @return [type] [description]
     */
//    public function getReservationsDateAttribute()
//    {
//        return $this->reservations()->get(['date_in', 'date_out', 'checked_out', 'checked_in'])->toArray();
//    }

    public function getReservationsDates()
    {
        return $this->reservations()->get(['date_in', 'date_out', 'checked_out', 'checked_in'])->toArray();
    }

    /**
     * [getReservationsCountAttribute description]
     * @return [type] [description]
     */
   public function getReservationsCount()
   {
       return $this->reservations()->count();
   }

//    public function getFutureReservationsCountAttribute(){
//        return   Reservation::where('team_id' , $this->team_id)->where('date_in' , '>' , now())->where('unit_id' , $this->id)->count();
//    }

//    public function getPastReservationAttribute(){
//        $checkOldReservation =  Reservation::where('team_id' , $this->team_id)->where('checked_in' , '!=' , null)->where('checked_out' , '=' , null)->where('unit_id' , $this->id)->orderBy('created_at' , 'desc')->first();
//
//        if($checkOldReservation){
//            $reservation = new \stdClass();
//            $reservation->id = $checkOldReservation['id'];
//            $reservation->number = $checkOldReservation['number'];
//
//            return $reservation;
//        }
//        return null;
//
//
//        return $checkOldReservation['id'] ?? null;
//    }

//    public function getOnlineReservationsCountAttribute(){
//
//        return count(OnlineReservation::where('unit_id' , $this->id)->where('team_id' , $this->team_id)->where('status' , 'waiting')->get());
//    }


//    public function getSettingsDayStartAttribute(){
//        return SettingRegister::getSettingItem('day_start')->getValue();
//    }
//
//    public function getSettingsVatAttribute(){
//        return SettingRegister::getSettingItem('tax')->getValue();
//    }
//
//    public function getSettingsTtxAttribute(){
//        return SettingRegister::getSettingItem('tourism_tax')->getValue();
//    }
//
//    public function getSettingsDayEndAttribute(){
//        return SettingRegister::getSettingItem('day_end')->getValue();
//    }

    /**
     * [getReservationByDay description]
     * @param  [type] $day [description]
     * @return [type]      [description]
     */
    public function getReservationByDay($day){
        return $this->reservations()->betweenDate($day)->first();
    }

    /**
     * Check Intersection
     * @note needed for new website and avoiding crashing corneer
     *
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    public function getReservationIntersection($start,$end)
    {
        // return $this->reservations()->intersection($start,$end)->first();
        return $this->reservations()->whereIntersectsDateIn($start)->whereIntersectsDateOut($end)->whereNull('checked_out')->first();
    }

    public function getReservationIntersectionCheck($start,$end)
    {
        // return $this->reservations()->intersection($start,$end)->first();
        return $this->reservations()->whereIntersectsDateIn($start)->whereIntersectsDateOutCheck($start,$end)->whereNull('checked_out')->first();
    }
   
    public static function boot()
    {
        parent::boot();
        //$units = Unit::all();
        // static::addGlobalScope('team_id', function (Builder $builder) {
        //     if(auth()->check())
        //         $builder->where('team_id', '=', request()->get('current_team_id', auth()->user()->current_team_id));
        // });

        self::creating(function ($model) {
            if(auth()->check()){
                $units_count = Unit::where('team_id',auth()->user()->current_team_id)->whereNull('deleted_at')->count();
                $team = Team::find(auth()->user()->current_team_id);
                $allowed_units_count_per_team = $team->allowed_units_count;
                if($units_count >= $allowed_units_count_per_team){
                    throw ValidationException::withMessages([
                        'message' => __('You can not add new units because you have reached the allowed units limit')
                    ]);
                }
            }
           
            $model->team_id = $model->team_id ?? auth()->user()->current_team_id;
            $unit_category = $model->unit_category ?? UnitCategory::latest()->first();
            if(empty($model->name)){
                $model->name = $unit_category->getTranslations('name');
            }
            $model->status = true;

            $model->general_features = $unit_category->general_features;
            $model->special_features = $unit_category->special_features;

            $model->sunday_day_price = $unit_category->sunday_day_price;
            $model->monday_day_price = $unit_category->monday_day_price;
            $model->tuesday_day_price = $unit_category->tuesday_day_price;
            $model->wednesday_day_price = $unit_category->wednesday_day_price;
            $model->thursday_day_price = $unit_category->thursday_day_price;
            $model->friday_day_price = $unit_category->friday_day_price;
            $model->saturday_day_price = $unit_category->saturday_day_price;

            $model->min_sunday_day_price = $unit_category->min_sunday_day_price;
            $model->min_monday_day_price = $unit_category->min_monday_day_price;
            $model->min_tuesday_day_price = $unit_category->min_tuesday_day_price;
            $model->min_wednesday_day_price = $unit_category->min_wednesday_day_price;
            $model->min_thursday_day_price = $unit_category->min_thursday_day_price;
            $model->min_friday_day_price = $unit_category->min_friday_day_price;
            $model->min_saturday_day_price = $unit_category->min_saturday_day_price;

            $model->month_price = $unit_category->month_price;
            $model->min_month_price = $unit_category->min_month_price;
        });

        self::addGlobalScope(new TeamScope());
    }

    /**
     * [hourPrice description]
     * @param  [type] $day [description]
     * @return [type]      [description]
     */
    public function hourPrice($hours=null)
    {
        if($hours){
            return round($this->sunday_hour_price * $hours, 2);
        }
        return $this->sunday_hour_price;
    }

    public function options()
    {
        $list = [];
        $options = $this->unit_options;
        if (!is_array($options)) {
            $options = json_decode($this->unit_options);
        }

        if (!empty($options)) {
            foreach ($options as $option) {
                if ($option)
                    $list[] = $option;
            }
        }

        return $list;
    }


    /**
     * Get the tourism tax percentage on rent price from settings
     * @return float|int
     */
    public function getTourismTax()
    {
        $tourismTax = getTourismPercentageForUnit($this->team_id);
        return $tourismTax ? (double)$tourismTax : 0;
    }

    /**
     * [getEwaTotal description]
     * @param  [type]  $total_price [description]
     * @param  boolean $formatted   [description]
     * @return [type]               [description]
     */
    public function getTourismTaxTotal($total_price,$formatted = true)
    {
        $tourismTax = $this->getTourismTax();
        $tourismTaxToPay = ($total_price / 100) * $tourismTax;
        return $formatted ? number_format($tourismTaxToPay, 2) : $tourismTaxToPay;
    }

    public function addMedia($file)
    {
        return $this->parentAddMedia($file)
            ->usingFileName($file->hashName());

    }

    public static function statusList()
    {
        return [
            self::STATUS_UNDER_CLEANING   =>  __('under cleaning'),
            self::STATUS_UNDER_MAINTENANCE    =>  __('under maintenance'),
        ];
    }

    public static function get_first_available($date)
    {
        $date = Carbon::parse($date);
        $unit = self::where('status', '!=', 0)->available($date)->first();
        if ($unit) {
            return $unit->id;
        }
        return null;
    }

    public function getMaintenanceInfoAttribute(){
       $maintenance = $this->maintenances->where('completed_by', null)->first();
       return $maintenance;
    }

    public function getDatesFromRangeWithOldPricesAndSpecialPrices($start, $end, array $prices, $rate, $old_rent_type = null, $rent_type = 'daily',$special_prices = [])
    {
       
        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        $total_price = 0;
        $min_total_price = 0;
        $min_day_price = 0;
        if ($rent_type == 2) {
            $total_price = $this->monthOldPrice($prices['prices']['month'], $period);
            $min_day_price = $this->minMonthOldPrice($prices['min_prices']['month'], $period , $prices['min_prices']);
            if ($old_rent_type == 2)
                $total_price = ($total_price + ($total_price * $rate) /100);
        }else {
            foreach ($period as $date) {
                $day_price = $this->dayOldPrice($date->format('l'), $prices['prices']['day']);
                $min_day_price = $this->minDayOldPrice($date->format('l'), $prices['min_prices']['day']);
                if ($old_rent_type == 1){

                    if (isset($special_prices[$date->format('Y-m-d')])) {
                        $day_price = $special_prices[$date->format('Y-m-d')];
                    } else {
                        $day_price = $this->dayOldPrice($date->format('l'), $prices['prices']['day']);
                    }
                    $day_price = ($day_price + ($day_price * $rate) /100);
                }

               
                $total_price += $day_price;
                $min_total_price += $min_day_price;
                $dates[] = [
                    'date' => $date->format('Y/m/d'),
                    'date_name' => __($date->format('l')),
                    'price_row' => $day_price,
                    'price' =>   $day_price
                ];
            }
        }
       
        $vat = 0;
        $ewa = 0;
        $ewa_formatted = '';
        $tourismTotal = 0;
        $tourismTotal_formatted = '';
        $ewaTax = 0;
        $vatTax = 0;
        $tourismTax = 0;

        if($prices['ewa_parentage']) {
            $ewaTax = $prices['ewa_parentage'];
            $ewaToPay = ($total_price / 100) * $ewaTax;
            $ewa = $ewaToPay;
            $ewa_formatted = number_format($ewaToPay, 2);
        }

        if($prices['vat_parentage']) {
            $vatTax = $prices['vat_parentage'];
            $vatToPay = (($total_price + $ewa) / 100) * $vatTax;
            $vat = $vatToPay;
            $vat_formatted = number_format($vat, 2);
        }

        if($prices['tourism_percentage']) {
            $tourismTax = $prices['tourism_percentage'];
            $tourismTaxToPay = ($total_price / 100) * $tourismTax;
            $tourismTotal = $tourismTaxToPay;
            $tourismTotal_formatted = number_format($tourismTaxToPay, 2);
        }

        $total_raw = floatval($total_price) + floatval($vat) + floatval($ewa) + floatval($tourismTotal) ;
//        $total = round($total_raw);
        $total_raw = sprintf("%.2f", $total_raw);
//        $total_raw = round($total_raw,1);

        $totals = [
            'currency' => 'SAR',
            'days' => $dates,
            'price' => $total_price,
            'vat_parentage' => $vatTax,
            'ewa_parentage' => $ewaTax,
            'sub_total' => $total_price,
            'min_sub_total' => $min_day_price,
            'total_vat' => $vat,
            'total_ewa' => $ewa,
            'total_price' => $total_raw,
            'total_price_raw' => $total_raw,
            'total_tourism' => $tourismTotal ,
            'tourism_percentage' => $tourismTax
        ];

        return $totals;
    }

    public function unitDayPrice($unit,$day)
    {
        switch ($day) {
            case "Sunday":
                return $unit->sunday_day_price;
                break;
            case "Monday":
                return $unit->monday_day_price;
                break;
            case "Tuesday":
                return $unit->tuesday_day_price;
                break;
            case "Wednesday":
                return $unit->wednesday_day_price;
                break;
            case "Thursday":
                return $unit->thursday_day_price;
                break;
            case "Friday":
                return $unit->friday_day_price;
                break;
            case "Saturday":
                return $unit->saturday_day_price;
                break;
            default:
                echo null;
        }
    }

    public function dailyByDayNamePrices()
    {
        return [
            'Sunday' => $this->sunday_day_price,
            'Monday' => $this->monday_day_price,
            'Tuesday' => $this->tuesday_day_price,
            'Wednesday' => $this->wednesday_day_price,
            'Thursday' => $this->thursday_day_price,
            'Friday' => $this->friday_day_price,
            'Saturday' => $this->saturday_day_price,
        ];
    }


    public function dayPriceFromSpecialPriceIfFound($date)
    {
        $special_prices_array = [];
        $special_prices = $this->unit_category && $this->unit_category->special_prices ? $this->unit_category->special_prices : [];
    
        if(count($special_prices)){
            foreach ($special_prices as $special_price) {
                $special_price_period = CarbonPeriod::create($special_price->start_date, $special_price->end_date);
                foreach ($special_price_period as $special_price_date) {
                    // saftey in case special prices added with empty days prices 
                    if (floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')])) {
                        $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($special_price->days_prices[Carbon::parse($special_price_date)->format('l')]);
                    } else {
                        $special_prices_array [Carbon::parse($special_price_date)->format('Y-m-d') ] = floatval($this->dayPrice(Carbon::parse($special_price_date)->format('l')));
                    }
                }
            }

            if(isset($special_prices_array[$date])){
                return $special_prices_array[$date];
            }else{
               return  $this->dayPrice(Carbon::parse($date)->format('l'));
            }

        }else{
            return  $this->dayPrice(Carbon::parse($date)->format('l'));
        }
    }

    public function getEwaTotalAutoRenewBasedOnOldPrices($total_price,$formatted = false,$percentage)
    {
        $ewa = $percentage;
        $ewaToPay = ($total_price / 100) * $ewa;
        return $formatted ? number_format($ewaToPay, 2) : $ewaToPay;
    }

    public function getVatTotalAutoRenewBasedOnOldPrices($total_price,$formatted = false,$vat_percentage,$ewa_percentage)
    {
        $vat = $vat_percentage;
        $vatToPay = (($total_price + $this->getEwaTotalAutoRenewBasedOnOldPrices($total_price,false,$ewa_percentage)) / 100) * $vat;
        return $formatted ? number_format($vatToPay, 2) : $vatToPay;
    }
}
