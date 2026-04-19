<?php

namespace App\Http\Resources\CustomerReviews;

use App\Reservation;
use App\Transaction;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class RatingMinifiedResource extends JsonResource
{
    public static $wrap = '';
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
            'customer_name' =>  $this->customer_name ,
            'email' => $this->reservation->customer->email,
            'avatar' => $this->customerAvatar($this->reservation->customer->email),
            'rating_value' => $this->rating_value,
            'positive_comment' => $this->q_seven,
            'negative_comment' => $this->q_eight,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d h:i A'),
        ];
    }

    function customerAvatar($email){
        $default = asset('default-avatar.png');
        if($email){
            return 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . '?s=200' . '&d=mp';
        }
        return  $default;
    }


}
