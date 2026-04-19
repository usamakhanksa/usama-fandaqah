<?php

namespace App\Http\Resources\CustomerReviews;

use App\Reservation;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class RatingResource extends JsonResource
{
    public function __construct($resource)
    {
        static::$wrap = null;
        parent::__construct($resource);
    }


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'hotel_name' => [
                'ar' => $this->reservation->team->name,
                'en' => !is_null($this->reservation->team->en_name) ? $this->reservation->team->en_name : $this->reservation->team->name
            ] ,
            'reservation_id' => $this->reservation_id,
            'reservation_number' => $this->reservation->number,
            'reservation_date_in' => $this->reservation->date_in,
            'reservation_date_out' => $this->reservation->date_out,
            'reservation_checked_in' => $this->reservation->checked_in,
            'reservation_checked_out' => $this->reservation->checked_out,
            'nights' => Carbon::parse($this->reservation->date_out)->diffInDays(Carbon::parse($this->reservation->date_in)),
            // 'unit_number' =>  $this->reservation->unitForRating->unit_number ,
            'unit_id' =>  $this->reservation->unit_id ,
            'customer_name' =>  $this->reservation->customer->name ,
            'customer_id' =>  $this->reservation->customer_id ,
            'customer_phone' => $this->reservation->customer ? $this->reservation->customer->phone : null,
            'q_one' => $this->q_one,
            'q_two' => $this->q_two,
            'q_three' => $this->q_three,
            'q_four' => $this->q_four,
            'q_five' => $this->q_five,
            'q_six' => $this->q_six,
            'positive_comment' => $this->q_seven,
            'negative_comment' => $this->q_eight,
            'q_nine' => $this->q_nine,
            'q_ten' => $this->q_ten,
            'q_eleven' => $this->q_eleven,
            'q_twelve' => $this->q_twelve,
            'q_custom' => $this->q_custom,
            'overall_rating' => $this->overall_rating,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }




}
