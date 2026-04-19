<?php

namespace App;

use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Traits\HasTeam;
use Carbon\CarbonPeriod;
use App\Scopes\TeamScope;
use Illuminate\Http\Request;
use App\Events\ReservationCreated;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmed;
use Illuminate\Support\Facades\DB;
use Spatie\WebhookServer\WebhookCall;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Model;
use Alfa6661\AutoNumber\AutoNumberTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnlineReservation extends Model
{
    use Rememberable, AutoNumberTrait, SoftDeletes, HasTeam;

    protected $fillable = [
        'customer_id',
        'date_in',
        'date_out',
        'team_id',
        'unit_id',
        'price',
        'nights',
        'provider',
        'public_api_consumer_id',
        'notes'
    ];

    protected $casts = [
        'date_in'   =>  'date',
        'date_out'   =>  'date',
    ];

    protected $with = [
        'customer',
        'unit',
        'team',
        'cancelled',
        'confirmed',
        'confirmedReservation'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('customer_id', function (Builder $builder) {
            $builder->whereHas('customer');
        });

        static::addGlobalScope('unit_id', function (Builder $builder) {
            $builder->whereHas('unit');
        });

        static::addGlobalScope(new TeamScope());
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function confirmedReservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id');
    }

    public function cancelled()
    {
        return $this->belongsTo(User::class, 'cancelled_by', 'id');
    }

    public function confirmed()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
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

    public function scopeDateInBetween($query, $date)
    {
        return $query->whereRaw('? between date_in and date_out', [$date]);
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
     * Scope a query to filter by Number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWaiting($query)
    {
        return $query->where('status' , 'waiting');
    }

    public function scopeCustomerName($query, $value)
    {
        return $query->whereHas('customer' , function($customer) use($value){
            $customer->where('name' , 'LIKE' , "%$value%") ;
        });
    }

    /**
     * @return array
     */
    public function getAutoNumberOptions()
    {
        return [
            'number' => [
                'format' => '?',
                'length' => 1
            ]
        ];
    }

    public function cancel($by)
    {
        $this->status = 'cancelled';
        $this->cancelled_at = new \DateTime();
        $this->cancelled_by = auth()->user()->id ?? null;
        $this->save();
        $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $this->team_id)->value('value');
        $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';
        $subject = 'تم الغاء حجزك رقم ';
        $subject .= $this->number;
        $subject .= ' - ';
        $subject .= $this->team->name;

        $url = $this->team->private_domain ? $this->team->private_domain :  $this->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');

        $data = [
            'to' => $this->customer->email,
            'reply_to' => $hotelEmail,
            'subject' => $subject,
            'html' => view('email.reservation_cancelled')->with(['reservation' =>  $this, 'url' =>  $url])->render(),
        ];

        $send = sendMailUsingMailMicroservice($data);
        // \Mail::to($this->customer)->send(new ReservationCancelled($this));
    }

    public function confirm()
    {

        $this->convertToReservation();
        $this->status = 'confirmed';
        $this->confirmed_at = new \DateTime();
        $this->confirmed_by = auth()->user()->id;

        $this->save();

        // Sending Notification to end-user of our api through their web-hooks
        if($this->provider == 'public_api'){

            // get the consumer
            $consumer =  PublicApiConsumer::find($this->public_api_consumer_id);

            // if the consumer provide a web hook url  from the beginning
            if($consumer->web_hook_url){

                // new Guzzle client
                $http = new Client();

                /**
                 * @description : Forming the request
                 * @note  :  i may use the Webhook-Token later to do some validation on the request
                 */
                $http->request('POST' , $consumer->web_hook_url , [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Webhook-Token' => $consumer->web_hook_token,
                    ],
                    'json' => [
                        'reservation' => [
                            'id' => $this->reservation_id ,
                            'date_in' => $this->date_in->toDateString(),
                            'date_out'=> $this->date_out->toDateString(),
                            'unit_name' => $this->unit->name,
                            'customer' => $this->customer->name ,
                            'status' => $this->status,
                            'nights' => $this->nights
                        ]
                    ],

                ]);
            }
        }

        return $this ;
    }

    private function convertToReservation()
    {
        $start = Carbon::parse($this->date_in);
        $end = Carbon::parse($this->date_out);
        $period = CarbonPeriod::create($start, $end);

        $reservations = self::query()->where('id', '!=', $this->id)
            ->where('status', '=', 'waiting')
            ->where('date_in', '>=', $this->date_in)
            ->where('date_out',     '<=', $this->date_out)
            ->where('unit_id' , $this->unit_id)
            ->get();


        $source = Source::query()->where('name->en', 'website')->get()->first();

        $data = \request('unit_reservation');

        $reservation = new Reservation();
        $reservation->team_id = $this->team_id;
        $reservation->unit_id = $this->unit_id;
        $reservation->customer_id = $this->customer_id;
        $reservation->date_in = $this->date_in;
        $reservation->date_out = $this->date_out;
        // attaching creator id for the reservation
        $reservation->created_by = auth()->user()->id;

        $reservation->source_id = $source->id;
        $reservation->total_price = $data['reservation']['prices']['total_price_raw'];
        $reservation->sub_total = $data['reservation']['prices']['price'];
        $reservation->vat_total = $data['reservation']['prices']['total_vat'];
        $reservation->ewa_total = $data['reservation']['prices']['total_ewa'];

        if ($reservation->save()) {
            $reservation->balance;
            $total_price = number_format($data['reservation']['prices']['total_price_raw'],2, '.', '');
            $reservation->forceWithdrawFloat($total_price, [
                'category' => 'reservation',
                'statement' => 'Reservation Total Price',
            ], true, false);
            $reservation->wallet->refreshBalance();
            event(new ReservationCreated($reservation));
        }
        $this->reservation_id = $reservation->id;

        collect($reservations)->each(function ($reservation) {
            $reservation->status = 'cancelled';
            $reservation->cancelled_at = new \DateTime();
            $reservation->reservation_id = $this->reservation_id;
            $reservation->save();

            // Web Hook Part
            if($reservation->provider == 'public_api'){

                // get the consumer
                $consumer =  PublicApiConsumer::find($reservation->public_api_consumer_id);

                // if the consumer provide a web hook url  from the beginning
                if($consumer->web_hook_url){

                    // new Guzzle client
                    $http = new Client();

                    /**
                     * @description : Forming the request
                     * @note  :  i may use the Webhook-Token later to do some validation on the request
                     */
                    $http->request('POST' , $consumer->web_hook_url , [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Webhook-Token' => $consumer->web_hook_token,
                        ],
                        'json' => [
                            'reservation' => [
                                'id' => $reservation->reservation_id ,
                                'date_in' => $reservation->date_in->toDateString(),
                                'date_out'=> $reservation->date_out->toDateString(),
                                'unit_name' => $reservation->unit->name,
                                'customer' => $reservation->customer->name ,
                                'status' => $reservation->status,
                                'nights' => $reservation->nights
                            ]
                        ],

                    ]);
                }
            }
        $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $reservation->team_id)->value('value');
        $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';
        $subject = 'تم الغاء حجزك رقم ';
        $subject .= $reservation->number;
        $subject .= ' - ';
        $subject .= $reservation->team->name;

        $url = $reservation->team->private_domain ? $reservation->team->private_domain :  $reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');

        $data = [
            'to' => $reservation->customer->email,
            'reply_to' => $hotelEmail,
            'subject' => $subject,
            'html' => view('email.reservation_cancelled')->with(['reservation' =>  $reservation, 'url' =>  $url])->render(),
        ];

        $send = sendMailUsingMailMicroservice($data);
            // \Mail::to($reservation->customer)->send(new ReservationCancelled($reservation));
        });
    }
}
