<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\ServiceLogResource;

class TransactionResource extends JsonResource
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
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'type' => $this->type,
            'transaction_flag' => $this->transaction_flag,
            'is_insurance' => $this->is_insurance,
            'amount' => $this->amount,
            'amount_without_tax' => $this->amount_without_tax,
            'enable_tax_on_withdraw' => $this->enable_tax_on_withdraw,
            'tax_percentage' => $this->tax_percentage,
            'tax_amount' => $this->tax_amount,
            'supplier_tax_number' => $this->supplier_tax_number,
            'invoice_number' => $this->invoice_number,
            'is_public' => $this->is_public,
            'is_promissory' => $this->is_promissory,
            'is_attached_to_invoice' => $this->is_attached_to_invoice,
            'kind' => $this->kind,
            'description' => $this->description,
            'confirmed' => $this->confirmed,
            'meta' => $this->meta,
            'number' => $this->number,
            'uuid' => $this->uuid,
            'correction_of_transaction_id' => $this->correction_of_transaction_id,
            'correction_reason' => $this->correction_reason,
            'reversed_at' => $this->reversed_at,
            'reversal_transaction_id' => $this->reversal_transaction_id,
            'is_advance_deposit' => $this->is_advance_deposit,
            'is_freezed' => $this->is_freezed,
            'business_date' => $this->business_date,
            'zatca_status' => $this->zatca_status,
            'zatca_invoice_id' => $this->zatca_invoice_id,
            'zatca_uuid' => $this->zatca_uuid,
            'zatca_qr_code' => $this->zatca_qr_code,
            'vat_calculation_basis' => $this->vat_calculation_basis,
            'vat_category' => $this->vat_category,
            'tourism_tax_amount' => $this->tourism_tax_amount,
            'accommodation_tax_amount' => $this->accommodation_tax_amount,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            'team_id' => $this->team_id,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
            'service_log' => new ServiceLogResource($this->whenLoaded('service_log')),
        ];
    }
}
