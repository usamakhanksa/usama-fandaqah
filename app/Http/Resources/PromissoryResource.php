<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PromissoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'serial' => $this->serial,
            'reservation_id' => $this->reservation_id,
            'team_id' => $this->team_id,
            'user_id' => $this->user_id,
            'total_amount' => $this->total_amount,
            'collected_amount' => $this->collected_amount,
            'status' => $this->status,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'due_location' => $this->due_location,
            'due_for' => $this->due_for,
            'due_owner' => $this->due_owner,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'fulfilled_at' => $this->fulfilled_at,
            'signature_status' => $this->signature_status,
            'unsigned_reason' => $this->unsigned_reason,
            'business_date' => $this->business_date,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'user' => new UserResource($this->whenLoaded('user')),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'promissory_payment_logs' => PromissoryPaymentLogResource::collection($this->whenLoaded('promissoryPaymentLogs')),
        ];
    }
}