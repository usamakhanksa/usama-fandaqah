<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JawalyLogResource extends JsonResource
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
            'status' => $this->status,
            'body' => $this->body,
            'numbers' => $this->numbers,
            'gate_message' => $this->gate_message,
            'response' => $this->response,
            'sender' => $this->sender,
        ];
    }


}
