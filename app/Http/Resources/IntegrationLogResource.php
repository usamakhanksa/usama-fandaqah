<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntegrationLogResource extends JsonResource
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
            'payload' => $this->payload,
            'response' => $this->response,
            'type' => $this->type,
            'team_id' => $this->team_id,
            'status' => $this->status == 1 ? 'processed' : 'failed',
            'action' => $this->getAction(),
            'created_at' => $this->created_at->toDateTimeString(),
            'reservation' => $this->reservation,
        ];
    }

    protected function getAction()
    {
        switch ($this->action) {
            case '1':
                return __('checkin');
                break;
            case '2':
                return __('update');
                break;
            case '3':
                return __('checkout');
                break;
            case '7':
                return __('checkOutGuest');
                break;
            
            default:
                # code...
                break;
        }
    }}
