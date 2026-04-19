<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;
use Vinkla\Hashids\Facades\Hashids;

class UnitsMovementResource extends JsonResource
{
    protected $date;


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
            'unit_name' => $this->unit()->withTrashed()->first()->name ?? null,
            'unit_number' => $this->unit()->withTrashed()->first()->unit_number ?? null,
            'reservation_number' => $this->number,
            'date_in' => $this->date_in,
            'date_out' => $this->date_out,
            'created_at' => $this->created_at,
            'customer' => $this->customer->name ?? null,
        ];
    }
}
