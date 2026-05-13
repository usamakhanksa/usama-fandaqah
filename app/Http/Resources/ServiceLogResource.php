<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceLogResource extends JsonResource
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
            'team_id' => $this->team_id,
            'user_id' => $this->user_id,
            'transaction_id' => $this->transaction_id,
            'type' => $this->type,
            'number' => $this->number,
            'amount' => $this->amount,
            'decimals' => $this->decimals,
            'meta' => $this->meta,
            'is_subtraction' => $this->is_subtraction,
            'active_note' => $this->active_note,
            'zatca_invoice_number' => $this->zatca_invoice_number,
            'is_freezed' => $this->is_freezed,
            'business_date' => $this->business_date,
            'correction_reason' => $this->correction_reason,
            'corrected_by' => $this->corrected_by,
            'zatca_status' => $this->zatca_status,
            'zatca_invoice_id' => $this->zatca_invoice_id,
            'zatca_uuid' => $this->zatca_uuid,
            'zatca_qr_code' => $this->zatca_qr_code,
            'zatca_response' => $this->zatca_response,
            'zatca_submitted_at' => $this->zatca_submitted_at,
            'zatca_accepted_at' => $this->zatca_accepted_at,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'transaction' => new TransactionResource($this->whenLoaded('transaction')),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
        ];
    }
}