<?php

namespace App;

use Illuminate\Validation\Rule;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Traits\LogsActivity;

class Guest extends Model
{
    use Rememberable, LogsActivity;

    protected $guarded = [];


    protected static $logName = 'guest';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributes = [
        'customer_id',
        'shomoos_id',
        'name',
        'relation_type' ,
        'id_type' ,
        'customer_type' ,
        'id_number' ,
        'birthday_date',
        'id_serial_number',
        'visa_number',
        'nationality_string',
        'id_type_string',
        'guest_type_string',
        'relation_type_string'
    ];


    /**
     * appends
     */
    protected $appends = [
        'guest_type_string',
        'id_type_string',
        'nationality_string',
        'relation_type_string'
    ];

    protected static $rules = [
        'id_number'    => 'unique:guests,id_number'
    ];

    protected $with = ['reservation'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * @return mixed|null
     */
    public function nationality()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return mixed|null
     */
    public function getIdTypesAttribute()
    {
        return Customer::idTypes();
    }

    /**
     * @return mixed|null
     */
    public function getCustomerTypesAttribute()
    {
        return Customer::customerTypes();
    }


    function relation_types()
    {
       return  collect([
            [
                'id' => 1,
                'name' => __('Son')
            ],
            [
                'id' => 2,
                'name' => __('Daughter')
            ],
            [
                'id' => 3,
                'name' => __('Wife')
            ],
            [
                'id' => 4,
                'name' => __('Brother')
            ],
            [
                'id' => 5,
                'name' => __('Sister')
            ],
            [
                'id' => 6,
                'name' => __('Father')
            ],
            [
                'id' => 7,
                'name' => __('Mother')
            ],
            [
                'id' => 8,
                'name' => __('Husband')
            ],
            [
                'id' => 9,
                'name' => __('Other')
            ],
        ]);
    }

    /**
     * @return mixed|null
     */
    public function getGuestTypeStringAttribute()
    {
        if (isset(Customer::customerTypes()[$this->customer_type]))
            return Customer::customerTypes()[$this->customer_type];
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
        return $this->id_types->where('id', $this->id_type)->first()['title'];
    }

    public function getRelationTypeStringAttribute()
    {
        return $this->relation_types()->where('id', $this->relation_type)->first()['name'];
    }



    public static function validate($input, $guest_id = null , $customer_id)
    {
        $rules = self::$rules;

        if ($guest_id != null) {
            // it's an update process
            // $rules['id_number'] .= ",$guest_id";
            return Validator::make($input, [
                'id_number' => [
                     Rule::unique('guests')->where(function ($query) use($guest_id,$customer_id) {
                       return $query
                                    ->where('customer_id', $customer_id);
                     })->ignore($guest_id)
                ],
            ]);
        }else{
            // its a create process

           return Validator::make($input, [
                'id_number' => [
                     Rule::unique('guests')->where(function ($query) use($customer_id) {
                       return $query->where('customer_id', $customer_id);
                     })
                ],
            ]);
        }

        // return Validator::make($input, $rules , self::messages());
    }
    public function scopeSearchByName1($query,$value)
    {
        return $query->where('name', 'like', '%' . $value . '%');
    }

    public function scopeSearchByIDNumber1($query,$value)
    {
        return $query->where('id_number', '=' , $value);
    }

    public function scopeSearchByEmail1($query,$value)
    {
        return $query->where('email', 'like', '%' . $value . '%');
    }

    public function scopeSearchByPhone1($query,$value)
    {
        return $query->where('phone','like', '%' . $value . '%');
    }

    static function messages(){
        return [
            'id_number.unique' => 'id number must be unique'
        ];
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class , 'customer_guest_reservation' , 'guest_id' , 'customer_id')->withPivot('reservation_id');
    }

    public function escortFromPivot(){
        return $this->hasOne(CustomerGuestReservation::class , 'guest_id');
    }
}
