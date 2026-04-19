<?php

namespace App\Http\Resources\Reports;

use Carbon\Carbon;
use App\Reservation;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Resources\UserResource;
use App\Http\Resources\SourceResource;
use App\Http\Resources\Reports\UnitResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeContractsResource extends JsonResource
{
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
            'reservation_hash_id' => Hashids::encode($this->id),
            'number' => $this->number,
            'reservation_type' => $this->reservation_type,
            'rent_type' => $this->rent_type,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'unit' => new UnitResource($this->unit),
            'customer' => $this->customer,
            'source' => $this->whenLoaded('source',function(){
                return new SourceResource($this->source);
            }),
            'employee_id' => $this->creator ?  $this->creator->id : null,
            'employee_name' => $this->creator ?  $this->creator->name : null,
            'company' => $this->company ? $this->company : null,
            'balance' => $this->balance / ($this->wallet->decimal_places == 2 ? 100 : 1000),
            'total_with_services' => number_format($this->total_price + $this->getServicesSum() , 2),
            'group_balance' => $this->company ? shareableGroupBalance($this) : null,
            'groupReservationBalanceMapper' => $this->groupReservationBalanceMapper,
        ];
    }
}
