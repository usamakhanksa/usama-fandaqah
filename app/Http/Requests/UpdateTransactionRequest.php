<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update transactions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $transactionId = $this->route('transaction');
        return [
            'payable_type' => 'sometimes|required|string|max:255',
            'payable_id' => 'sometimes|required|integer',
            'wallet_id' => 'nullable|integer',
            'type' => 'sometimes|required|in:deposit,withdraw',
            'transaction_flag' => 'nullable|string|max:50',
            'is_insurance' => 'boolean',
            'amount' => 'sometimes|required|numeric|min:0',
            'amount_without_tax' => 'nullable|numeric|min:0',
            'enable_tax_on_withdraw' => 'boolean',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'supplier_tax_number' => 'nullable|string|max:50',
            'invoice_number' => 'nullable|string|max:100',
            'is_public' => 'boolean',
            'is_promissory' => 'boolean',
            'is_attached_to_invoice' => 'boolean',
            'kind' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:65535',
            'confirmed' => 'boolean',
            'meta' => 'nullable|array',
            'receiver_bank_id' => 'nullable|integer',
            'uuid' => 'nullable|string|max:36',
            'is_advance_deposit' => 'boolean',
            'is_freezed' => 'boolean',
            'cashier_shift_id' => 'nullable|integer|exists:cashier_shifts,id',
            'zatca_status' => 'nullable|in:pending,submitted,accepted,rejected',
            'zatca_invoice_id' => 'nullable|string|max:100',
            'zatca_uuid' => 'nullable|string|max:36',
            'zatca_qr_code' => 'nullable|string|max:500',
            'vat_calculation_basis' => 'nullable|in:inclusive,exclusive',
            'vat_category' => 'nullable|in:standard,zero-rated,exempt',
            'tourism_tax_amount' => 'nullable|numeric|min:0',
            'accommodation_tax_amount' => 'nullable|numeric|min:0',
            'business_date' => 'nullable|date',
            'correction_reason' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'payable_type' => __('Payable Type'),
            'payable_id' => __('Payable ID'),
            'wallet_id' => __('Wallet ID'),
            'type' => __('Transaction Type'),
            'transaction_flag' => __('Transaction Flag'),
            'is_insurance' => __('Is Insurance'),
            'amount' => __('Amount'),
            'amount_without_tax' => __('Amount Without Tax'),
            'enable_tax_on_withdraw' => __('Enable Tax on Withdraw'),
            'tax_percentage' => __('Tax Percentage'),
            'tax_amount' => __('Tax Amount'),
            'supplier_tax_number' => __('Supplier Tax Number'),
            'invoice_number' => __('Invoice Number'),
            'is_public' => __('Is Public'),
            'is_promissory' => __('Is Promissory'),
            'is_attached_to_invoice' => __('Is Attached to Invoice'),
            'kind' => __('Kind'),
            'description' => __('Description'),
            'confirmed' => __('Confirmed'),
            'meta' => __('Meta'),
            'receiver_bank_id' => __('Receiver Bank ID'),
            'uuid' => __('UUID'),
            'is_advance_deposit' => __('Is Advance Deposit'),
            'is_freezed' => __('Is Freezed'),
            'cashier_shift_id' => __('Cashier Shift ID'),
            'zatca_status' => __('ZATCA Status'),
            'zatca_invoice_id' => __('ZATCA Invoice ID'),
            'zatca_uuid' => __('ZATCA UUID'),
            'zatca_qr_code' => __('ZATCA QR Code'),
            'vat_calculation_basis' => __('VAT Calculation Basis'),
            'vat_category' => __('VAT Category'),
            'tourism_tax_amount' => __('Tourism Tax Amount'),
            'accommodation_tax_amount' => __('Accommodation Tax Amount'),
            'business_date' => __('Business Date'),
            'correction_reason' => __('Correction Reason'),
        ];
    }
}