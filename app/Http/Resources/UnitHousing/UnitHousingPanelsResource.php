<?php

namespace App\Http\Resources\UnitHousing;

use Carbon\Carbon;
use App\Reservation;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Resources\UserResource;
use App\Http\Resources\SourceResource;
use App\Http\Resources\Reports\UnitResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitHousingPanelsResource extends JsonResource
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
            'rid' => $this->id,
            'rnum' => $this->number,
            'rdi' => $this->date_in,
            'rdo' => $this->date_out,
            'rchi' => $this->checked_in,
            'rcho' => $this->checked_out,
            'total_price' => $this->total_price,
            'services_sum' => $this->getServicesSum(),
            'reservation_type' => $this->reservation_type,
            'reservation_hash_id' => Hashids::encode($this->id),
            'rent_type' => $this->rent_type,
            'status' => $this->status,
            'uid' => $this->unit->id,
            'unum' => $this->unit->unit_number,
            'cid' => $this->customer ? $this->customer->id : null,
            'cname' =>  $this->customer ? $this->customer->name : null,
            'cphone' => $this->customer ? $this->customer->phone : null,
            'company_name' => $this->company ? $this->company->name : null,
            'company_phone' => $this->company ? $this->company->phone : null,
            'rb' => $this->balance / ($this->wallet->decimal_places == 3 ? 1000 : 100) ,
            'group_balance' => $this->company ? shareableGroupBalance($this) : null,
            'groupReservationBalanceMapper' => $this->groupReservationBalanceMapper,
            'team_id' => $this->team_id
        ];
    }
}
