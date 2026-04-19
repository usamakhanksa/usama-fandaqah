<?php

namespace App\Http\Resources\ReservationManagement;

use App\Reservation;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Vinkla\Hashids\Facades\Hashids;

class CompanyGroupReservationResource extends JsonResource
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
            'balance' => $this->balance,
            'ids' => $this->data['ids']
        ];
    }




}
