<?php

namespace App\Http\Resources\ReservationManagement;

use Carbon\Carbon;
use App\Reservation;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyReservationResource extends JsonResource
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
            'company_id' => $this->company_id,
            'group_reservation_id' => $this->group_reservation_id,
            'reservation_number' => $this->number,
            'customer_name' => $this->customer ? $this->customer->name : '-',
            'customer_id' => $this->customer ? $this->customer->id : null,
            'unit_name' => $this->unit ? $this->unit->name : '-',
            'unit_number' => $this->unit ? $this->unit->unit_number : '-',
            'unit_id' => $this->unit ? $this->unit->id : null,
            'status' => $this->status,
            'rent_type' => $this->rent_type,
            'checked_in' => $this->checked_in,
            'checked_out' => $this->checked_out,
            'date_in' => Carbon::parse($this->date_in)->format('Y/m/d'),
            'date_out' => Carbon::parse($this->date_out)->format('Y/m/d'),
            'balance' => $this->wallet->decimal_places == 3 ? $this->balance / 1000 : $this->balance / 100,
            'invoices' => $this->invoices
        ];
    }




}
