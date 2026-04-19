<?php

namespace App\Http\Resources;

use App\Http\Resources\ReservationResource;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
            'body' => $this->body,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d'),
            'time_created' => Carbon::parse($this->created_at)->format('H:i A'),
            'user' => $this->created_by,
            'user_is_admin' => $this->created_by->isAdmin()
        ];
    }
}
