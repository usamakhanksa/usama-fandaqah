<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Super Admin bypasses all permission checks
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->can('receipts.edit');
    }

    public function rules(): array
    {
        $receipt = $this->route('receipt');
        
        // Prevent updates if receipt is not in draft status
        if ($receipt && !$receipt->canBeEdited()) {
            return [
                '_status_check' => ['required', 'in:draft'], // This will always fail
            ];
        }

        return [
            'reservation_id' => ['nullable', 'integer', 'exists:reservations,id'],
            'guest_id' => ['nullable', 'integer', 'exists:guests,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'transaction_id' => ['nullable', 'integer', 'exists:transactions,id'],
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3', Rule::in(['SAR', 'USD', 'EUR', 'GBP', 'AED'])],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'payment_method' => ['nullable', 'string', Rule::in(['cash', 'card', 'bank_transfer', 'cheque', 'online', 'other'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'cheque_number' => ['nullable', 'string', 'max:255'],
            'card_last_four' => ['nullable', 'string', 'size:4'],
            'description' => ['nullable', 'string', 'max:1000'],
            'receipt_date' => ['nullable', 'date'],
            'receipt_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            '_status_check.in' => 'Only draft receipts can be edited.',
            'amount.min' => 'Amount must be greater than 0.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'card_last_four.size' => 'Card last four must be exactly 4 digits.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Calculate amount_base if amount or exchange_rate changed
        if ($this->has('amount') || $this->has('exchange_rate')) {
            $amount = (float) ($this->input('amount') ?? $this->route('receipt')?->amount ?? 0);
            $exchangeRate = (float) ($this->input('exchange_rate') ?? $this->route('receipt')?->exchange_rate ?? 1);
            $this->merge([
                'amount_base' => round($amount * $exchangeRate, 2),
            ]);
        }
    }
}
