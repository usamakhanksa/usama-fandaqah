<?php

namespace App;

use Laravelista\Comments\Commentable;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Thomasjohnkane\Snooze\Traits\SnoozeNotifiable;
use App\Scopes\TeamScope;

class Customer extends Model
{
    use Rememberable;
    use SoftDeletes;
    use LogsActivity;
    use Notifiable;
    use SnoozeNotifiable;
    use Commentable;

    /**
     * table
     */
    protected $table = 'customer';

    /**
     * fillable
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'id_number',
        'birthday_date',
        'id_expire_date',
        'country_id',
        'id_type',
        'customer_type',
        'gender',
        'team_id',
        'highlight_id',
        'highlight_color',
        'id_serial_number',
        'coming_away',
        'visa_number',
        'work',
        'work_phone',
        'address',
        'token'
    ];

    protected static $logAttributes = ['name', 'phone', 'email', 'customer_type_string', 'id_type_string', 'nationality_string', 'id_number'];

    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    /**
     * appends
     */
    protected $appends = [
        'reservations_count',
        'customer_types',
        'customer_type_string',
        'id_types',
        'id_type_string',
        'nationality_string',
        'label'
    ];

    /**
     * casts
     */
    protected $casts = [
        //        'id_expire_date' => 'date',
        //        'birthday_date' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    /**
     * ID Types
     */
    public const STATUS_CARD = 1;
    public const STAY = 2;
    public const PASSPORT = 3;

    /**
     * SCTH TYPES
     */
    public const TYPE_CITIZEN = 1;
    public const TYPE_GULF_CITIZEN = 2;
    public const TYPE_VISITOR = 3;
    public const TYPE_RESIDENT = 4;

    /**
     * SCTH purpose of visit
     */
    public const PURPOSE_TOURISM = 1;
    public const PURPOSE_FAMILY_OR_FRIENDS = 2;
    public const PURPOSE_RELIGIOUS = 3;
    public const PURPOSE_BUSINESS_OR_WORK = 4;
    public const PURPOSE_SPORTS = 5;
    public const PURPOSE_ENTERTAINMENT = 6;
    public const PURPOSE_OTHER = 7;
    public const PURPOSE_DIWAN_EMPLOYEE = 8;
    public const QUARANTINED_GUESTS = 9;
    public const MINISTRY_OF_HEALTH_STAFF = 10;

    protected static function boot()
    {
        if (\Auth::check()) {
            $team_id = (auth()->user()->current_team_id) ? auth()->user()->current_team_id : 0;
            $team_id = request()->get('current_team_id', $team_id);
            // auto-sets values on creation
            static::creating(function ($query) use ($team_id) {
                if (empty($query->team_id)) {
                    $query->team_id = $team_id;
                }
            });
        }
        //static::addGlobalScope(new TeamScope());
        parent::boot();
    }

    /**
     * purpose for dropdowns
     * @return array
     */
    public static function purposeOfVisit()
    {
        return [
            self::PURPOSE_TOURISM => __('Tourism'),
            self::PURPOSE_FAMILY_OR_FRIENDS => __('Family or Friends'),
            self::PURPOSE_RELIGIOUS => __('Religious'),
            self::PURPOSE_BUSINESS_OR_WORK => __('Business or Work'),
            self::PURPOSE_SPORTS => __('Sports'),
            self::PURPOSE_ENTERTAINMENT => __('Entertainment'),
            self::PURPOSE_OTHER => __('Other'),
            self::PURPOSE_DIWAN_EMPLOYEE => __('Diwan employee'),
            self::QUARANTINED_GUESTS => __('Quarantined guests'),
            self::MINISTRY_OF_HEALTH_STAFF => __('Ministry of Health Staff'),
        ];
    }

    /**
     * Type list for dropdowns
     * @return array
     */
    public static function customerTypes()
    {
        return [
            self::TYPE_CITIZEN => __('Citizen'),
            self::TYPE_GULF_CITIZEN => __('Gulf Citizen'),
            self::TYPE_VISITOR => __('Visitor'),
            self::TYPE_RESIDENT => __('Resident'),
        ];
    }

    /**
     * Type list for dropdowns
     * @return array
     */
    public static function idTypes()
    {
        return collect([
            [
                'id' => 1,
                'title' => __('National ID')
            ],
            [
                'id' => 2,
                'title' => __('Card family ID')
            ],
            [
                'id' => 3,
                'title' => __('GCC ID')
            ],
            [
                'id' => 4,
                'title' => __('Residence')
            ],
            [
                'id' => 5,
                'title' => __('Passport')
            ],
            [
                'id' => 6,
                'title' => __('Visit identity')
            ],

        ]);
    }

    /**
     * @return mixed|null
     */
    public function getIdTypesAttribute()
    {
        return self::idTypes();
    }

    /**
     * @return mixed|null
     */
    public function getCustomerTypesAttribute()
    {
        return self::customerTypes();
    }

    /**
     * @return mixed|null
     */
    public function getCustomerTypeStringAttribute()
    {
        if (isset(self::customerTypes()[$this->customer_type])) {
            return self::customerTypes()[$this->customer_type];
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getNationalityStringAttribute()
    {
        return $this->nationality->title ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getIdTypeStringAttribute()
    {
        return $this->id_types->where('id', $this->id_type)->first() ? $this->id_types->where('id', $this->id_type)->first()['title'] : null;
    }

    public function getIdTypeObject()
    {
        return $this->id_types->where('id', $this->id_type)->first() ? $this->id_types->where('id', $this->id_type)->first() : null;
    }

    /**
     * @return mixed|null
     */
    public function nationality()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return int
     */
    public function getReservationsCountAttribute()
    {
        return Reservation::where(['customer_id' => $this->id])->where('team_id', $this->team_id)->count();
    }

    public function highlight()
    {
        return $this->belongsTo(Highlight::class);
    }

    public function getLabelAttribute()
    {
        if (!$this->highlight) {
            return '';
        }
        $style = "border-color: {$this->highlight->color};background: {$this->highlight->color}50;";

        return '<label ref="customer_label" class="customer-label" id="customer-label" style="' . $style . '">' . $this->highlight->name . '</label>';
    }

    public function onlineReservations()
    {
        return $this->hasMany(OnlineReservation::class, 'customer_id', 'id')
            ->where('team_id', '!=', null);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function scopeByIdNumber($query, $value)
    {
        return $query->where('id_number', 'LIKE', "%$value%");
        ;
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function scopeByPhoneNumber($query, $value)
    {
        return $query->where('phone', 'LIKE', "%$value%");
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function scopeByGender($query, $value)
    {
        return $query->where('gender', '=', $value);
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function scopeBySearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('phone', 'like', '%' . $value . '%')
            ->orWhere('email', 'like', '%' . $value . '%')
            ->orWhere('id_number', 'like', '%' . $value . '%');
    }

    public function scopeSearchByName($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    public function scopeSearchByIDNumber($query, $value)
    {
        return $query->where('id_number', '=', $value);
    }

    public function scopeSearchByEmail($query, $value)
    {
        return $query->where('email', 'like', '%' . $value . '%');
    }

    public function scopeSearchByPhone($query, $value)
    {
        return $query->where('phone', 'like', '%' . $value . '%');
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function scopeByRegistrationDate($query, $value)
    {
        if (!is_array($value)) {
            $value = func_get_args();
            array_shift($value);
        }

        $from = date('Y-m-d', strtotime($value[0]));
        $to = date('Y-m-d', strtotime($value[1]));
        return $query->where('created_at', '>=', $from)->where('created_at', '<=', $to);
    }

    public function reservations()
    {
        return $this->hasMany('App\Reservation', 'customer_id')->orderByDesc('id');
    }

    public function comments()
    {
        return $this->morphMany(config('comments.model'), 'commentable')->whereHasMorph('commenter', [User::class], function ($u) {
            $u->where('current_team_id', auth()->user()->current_team_id);
        })->orderBy('created_at', 'desc');
    }

    public static function validate($input, $id = null)
    {
        if ($id != null) {
            $rules = ['id_number' => "unique:customer,id_number,{$id},id,deleted_at,NULL"];
        } else {
            $rules = ['id_number' => 'unique:customer,id_number,NULL,id,deleted_at,NULL'];
        }

        return Validator::make($input, $rules, self::messages());
    }

    public static function messages()
    {
        return [
            'id_number.unique' => 'id number must be unique'
        ];
    }

    public function guests()
    {
        // return $this->hasMany(Guest::class , 'customer_id')->whereNull('reservation_id')->distinct();
        return $this->hasMany(Guest::class, 'customer_id')->distinct();
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id')->withTrashed() ;
    }
}
