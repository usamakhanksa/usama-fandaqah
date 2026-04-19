<?php

namespace App\Http\Resources\ReservationManagement;

use App\Reservation;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ReservationResource extends JsonResource
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
        $this->wallet->refreshBalance();
        return [
            'id' => $this->id,
            'reservation_number' => $this->number,
            'customer_name' => $this->customer ? $this->customer->name : '-',
            'customer_id' => $this->customer ? $this->customer->id : null,
            'unit_name' => $this->unit ? $this->unit->name : '-',
            'unit_number' => $this->unit ? $this->unit->unit_number : '-',
            'unit_id' => $this->unit ? $this->unit->id : null,
            'unit_status' => $this->unit ? $this->unit->status : null,
            'status' => $this->status,
            'rent_type' => $this->rent_type,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'nights' => $this->nights > 0 ? $this->nights : 1,
            'leasing_price' => $this->nights > 0 ? $this->sub_total / $this->nights : $this->sub_total,
            'services_price' => abs($this->getServicesWithoutTaxesSum()),
            'amount' => $this->sub_total + $this->getServicesWithoutTaxesSum(),
            'taxes' => number_format($this->ewa_total + $this->vat_total + $this->ttx_total  + $this->getServicesTaxesSum(), 2),
            'total' => $this->total_price + $this->getServicesSum(),
            'paid' => $this->getDepositSum() - $this->getWithdrawSum(),
            'balance' => $this->wallet->decimal_places == 3 ? $this->balance / 1000 : $this->balance / 100,
            'checked_in' => $this->checked_in,
            'checked_out' => $this->checked_out,
            'shomos_status' => $this->shomos_status,
            'reservation_type' => $this->reservation_type,
            'attachable_id' => $this->attachable_id,
            'attachable_reservations_count' =>  count($this->attachedReservations()),
            'company' => $this->company,
            'group_balance' => $this->company ? shareableGroupBalance($this) : null,
            'groupReservationBalanceMapper' => $this->groupReservationBalanceMapper,
            'source' => $this->source ? $this->source->name : '-',
            'source_num' => $this->source_num,
            'shomos_id' => $this->shomoos_id,
            'services_included' => $this->reservationFreeServices
        ];
    }
}
