<?php

namespace App\Http\Resources\ReservationManagement;

use App\Reservation;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ReservationGuestsResource extends JsonResource
{

    public function __construct($resource)
    {
        self::wrap('');
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
            'adults_count' => $this->adults_count > 0 ? $this->adults_count + 1 :  1 ,
            'childrens_count' => $this->childrens_count
        ];
    }




}
