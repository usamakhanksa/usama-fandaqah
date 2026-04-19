<?php

namespace App;

use App\Traits\HasTeam;
use Carbon\CarbonPeriod;
use Spatie\MediaLibrary\Models\Media;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\STAAH\UnitCategoryHandlerJob;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Jobs\RefreshFeaturedCategoriesOnCustomerWebsite;
use App\Scopes\TeamScope;

class UnitCategory extends Model implements HasMedia
{
    use Rememberable;
    use SoftDeletes;
    use HasTranslations;
    use HasTeam;
    use LogsActivity;

    use HasMediaTrait {
        HasMediaTrait::addMedia as parentAddMedia;
    }

    public $translatable = ['name', 'description', 'short_description'];

    public const TYPE_SINGLE_BED_ROOM = 1;
    public const TYPE_DOUBLE_BED_ROOM = 2;
    public const TYPE_REGULAR_SUITE = 3;
    public const TYPE_STUDIO_APARTMENT = 4;
    public const TYPE_FURNISHED_APARTMENT_WITH_2_ROOMS = 5;
    public const TYPE_FURNISHED_APARTMENT_WITH_3_ROOMS = 6;
    public const TYPE_VILLA = 7;
    public const TYPE_ROOM_WITH_3_BEDS = 8;
    public const TYPE_ROOM_WITH_4_BEDS = 9;
    public const TYPE_ROOM_WITH_5_BEDS = 10;
    public const TYPE_ROOM_WITH_6_BEDS = 11;
    public const TYPE_ROOM_WITH_7_BEDS_OR_MORE = 12;
    public const TYPE_OTHER = 13;
    public const TYPE_EXECUTIVE_LUXURY_SUITE = 14;

    public static function boot()
    {
        parent::boot();
        self::addGlobalScope(new TeamScope());

        self::created(function ($model) {
            UnitCategoryHandlerJob::dispatch($model->team_id,$model->id);
        });
        
        self::updated(function ($model) {
            UnitCategoryHandlerJob::dispatch($model->team_id,$model->id);
            RefreshFeaturedCategoriesOnCustomerWebsite::dispatch($model);
        });
    }

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

    public static function types()
    {
        return [
            self::TYPE_OTHER => __('Other'),
            self::TYPE_SINGLE_BED_ROOM => __('Single Bed Room'),
            self::TYPE_DOUBLE_BED_ROOM => __('Double Bed Room / Kind Bed Room'),
            self::TYPE_REGULAR_SUITE => __('Regular Suite'),
            self::TYPE_STUDIO_APARTMENT => __('Studio Apartment / Apartment with Single Room'),
            self::TYPE_FURNISHED_APARTMENT_WITH_2_ROOMS => __('Furnished Apartment with 2 rooms'),
            self::TYPE_FURNISHED_APARTMENT_WITH_3_ROOMS => __('Furnished Apartment with 3 rooms'),
            self::TYPE_VILLA => __('Villa'),
            self::TYPE_ROOM_WITH_3_BEDS => __('Room with 3 beds'),
            self::TYPE_ROOM_WITH_4_BEDS => __('Room with 4 beds'),
            self::TYPE_ROOM_WITH_5_BEDS => __('Room with 5 beds'),
            self::TYPE_ROOM_WITH_6_BEDS => __('Room with 6 beds'),
            self::TYPE_ROOM_WITH_7_BEDS_OR_MORE => __('Room with 7 beds or more '),
            self::TYPE_EXECUTIVE_LUXURY_SUITE => __('Executive / Luxury Suite'),
        ];
    }

    protected $attributes = [
        'order' => 0,
        'general_features' => 'array',
        'special_features' => 'array',
        'sunday_day_price' => 0,
        'monday_day_price' => 0,
        'tuesday_day_price' => 0,
        'wednesday_day_price' => 0,
        'thursday_day_price' => 0,
        'friday_day_price' => 0,
        'saturday_day_price' => 0,
        'month_price' => 0,
        'hour_price' => 0,
        'min_sunday_day_price' => 0,
        'min_monday_day_price' => 0,
        'min_tuesday_day_price' => 0,
        'min_wednesday_day_price' => 0,
        'min_thursday_day_price' => 0,
        'min_friday_day_price' => 0,
        'min_saturday_day_price' => 0,
        'min_month_price' => 0
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'unit_category_id', 'id')->whereEnabled(true)->where('status', 1)->whereNull('deleted_at');
    }

    public function allUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'unit_category_id', 'id')->whereEnabled(true)->whereNull('deleted_at');
    }

    public function dayPrice($day)
    {
        switch ($day) {
            case 'Sunday':
                return $this->sunday_day_price;
                break;
            case 'Monday':
                return $this->monday_day_price;
                break;
            case 'Tuesday':
                return $this->tuesday_day_price;
                break;
            case 'Wednesday':
                return $this->wednesday_day_price;
                break;
            case 'Thursday':
                return $this->thursday_day_price;
                break;
            case 'Friday':
                return $this->friday_day_price;
                break;
            case 'Saturday':
                return $this->saturday_day_price;
                break;
            default:
                echo null;
        }
    }

    public function monthPrice($period = null)
    {
        if (isset($period)) {
            $days = count($period);
            $monthly_single_days = SettingRegister::getSettingItem('monthly_single_days')->getValue();
            if ($monthly_single_days) {
                $month_count = floor($days / 30);
                $total_price = round($this->month_price * $month_count, 2);
                foreach ($period as $key => $date) {
                    if ($key > ($month_count * 30) - 1) {
                        $day_price = $this->dayPrice($date->format('l'));
                        $total_price += $day_price;
                    }
                }
                return $total_price;
            } else {
                return round($this->month_price * $days / 30, 2);
            }
        }
        return $this->month_price;
    }

    public function dailyPrices()
    {
        return [
            'sunday_day_price' => $this->sunday_day_price,
            'monday_day_price' => $this->monday_day_price,
            'tuesday_day_price' => $this->tuesday_day_price,
            'wednesday_day_price' => $this->wednesday_day_price,
            'thursday_day_price' => $this->thursday_day_price,
            'friday_day_price' => $this->friday_day_price,
            'saturday_day_price' => $this->saturday_day_price,
        ];
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

    public function scopeWhereHasUnits($query)
    {
        return $query->whereHas('units');
    }

    public function getDatesFromRange($start, $end, $rent_type = 'daily')
    {
        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        foreach ($period as $date) {
            $day_price = $this->dayPrice($date->format('l'));
            $dates[] = [
                'date' => $date->format('Y/m/d'),
                'date_name' => __($date->format('l')),
                'price_row' => $day_price,
                // 'price' => money_format('%i', $day_price),
                // 'price' => $day_price
            ];
        }

        return ['days' => $dates];
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function getGeneralFeaturesArrayAttribute()
    {
        return explode(',', $this->general_features);
    }

    public function getSpecialFeaturesArrayAttribute()
    {
        return explode(',', $this->special_features);
    }

    public function getDatesCustom($start, $end, $rent_type = 'daily')
    {
        if ($start != $end) {
            $end->subDay();
        }
        $period = CarbonPeriod::create($start, $end);
        $dates = [];
        foreach ($period as $date) {
            $day_price = $this->dayPrice($date->format('l'));
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'price' => $day_price,
            ];
        }

        return $dates;
    }

    public function available_to_sync_units(): HasMany
    {
        return $this->hasMany(Unit::class, 'unit_category_id', 'id')
        ->where('available_to_sync', 1)
        ->whereEnabled(true)
        ->where('status', 1)
        ->whereNull('deleted_at');
    }

    public function special_prices(){
        return $this->hasMany(SpecialPrice::class)->whereNull('deleted_at')->whereEnabled(true);
    }

    public function unitsForUpdateModal(): HasMany
    {
        return $this->hasMany(Unit::class, 'unit_category_id', 'id')->whereEnabled(true)->whereNull('deleted_at');
    }
}
