<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use Watson\Rememberable\Rememberable;

class ReservationGuests extends Model
{
    use Rememberable, LogsActivity;

    protected $fillable = [
    	'name',
    	'gender',
    	'relation_type',
    	'id_number',
    	'reservation_id',
    	'customer_id',
		'id_type',
		'customer_type',
		'country_id',
		'id_serial_number',
		'visa_number',
		'shomoos_id'
	];

    /**
     * appends
     */
    protected $appends = [
        'customer_type_string',
        'id_type_string',
        'nationality_string',
    ];

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

    /**
     * @return mixed|null
     */
    public function getCustomerTypeStringAttribute()
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
}
