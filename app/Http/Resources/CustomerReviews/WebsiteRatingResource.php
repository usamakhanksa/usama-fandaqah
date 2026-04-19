<?php

namespace App\Http\Resources\CustomerReviews;

use App\Reservation;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class WebsiteRatingResource extends JsonResource
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
            'reservation_number' => $this->reservation->number,
            'nights' => Carbon::parse($this->reservation->date_out)->diffInDays(Carbon::parse($this->reservation->date_in)),
            'unit_number' =>  $this->reservation->unit->unit_number ,
            'unit_name' =>  $this->reservation->unit->getTranslations('name') ,
            'customer_name' =>  $this->reservation->customer->name ,
            'positive_comment' => $this->q_seven,
            'negative_comment' => $this->q_eight,
            'overall_rating' => $this->overall_rating, 
            'rating_date' => [
                'ar' => Carbon::parse($this->created_at)->format('d') . ' ' . Carbon::parse($this->created_at)->getTranslatedMonthName() . ' , ' . Carbon::parse($this->created_at)->format('Y'),
                'en' =>  Carbon::parse($this->created_at)->format('d F, Y')
            ],
            'reservation_date' => [
                'ar' => Carbon::parse($this->reservation->date_in)->getTranslatedMonthName() . ' ' . Carbon::parse($this->reservation->date_in)->format('Y'),
                'en' => Carbon::parse($this->reservation->date_in)->format('F Y')
            ] 
        ];
    }




}
