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

class ReservationSourcesResource extends JsonResource
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
            'checked_in' => $this->checked_in,
            'checked_out' => $this->checked_out,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'nights' => $this->getNights(),
            // 'over_out_nights' => $this->getOverOutNights(),
            'purpose_of_visit' => $this->purpose_of_visit,
            'unit' => new UnitResource($this->unit),
            'customer' => $this->customer,
            'source' => $this->whenLoaded('source',function(){
                return new SourceResource($this->source);
            }),
            'source_number' => $this->source_num,
            'deleted_at' => $this->deleted_at,
            'company' => $this->company ? $this->company : null,
            'balance' => $this->balance / ($this->wallet->decimal_places == 2 ? 100 : 1000),
            'group_balance' => $this->company ? shareableGroupBalance($this) : null,
            'groupReservationBalanceMapper' => $this->groupReservationBalanceMapper,
        ];
    }
}
