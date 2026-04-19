<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
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
            'start_at' => ($this->start_at) ? $this->start_at->timestamp : null,
            'completed_at' => ($this->completed_at) ? $this->completed_at->timestamp : null,
            'unit_id' => $this->unit_id,
            'creator_id' => $this->created_by,
            'note' => $this->note,
            'time_spent' => $this->time_spent,
            'creator' => new UserResource($this->creator),
            'completed_by_id' => $this->completed_by,
            'completed_by' => new UserResource($this->completedBy),
            'unit' => new UnitResource(Unit::withOutGlobalScope('team_id')->withTrashed()->find($this->unit_id))
        ];
    }
}
