<?php

namespace App\Http\Resources\Index;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoicesIndexResource extends JsonResource
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
            'reservation_id' => $this->reservation_id,
            'team_id' => $this->team_id ,
            'from' => $this->from ,
            'to' => $this->to
        ];
    }


}
