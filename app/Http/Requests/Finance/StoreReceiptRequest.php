<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Super Admin bypasses all permission checks
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->can('receipts.create');
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['nullable', 'integer', 'exists:reservations,id'],
            'guest_id' => ['required_without:company_id', 'nullable', 'integer', 'exists:guests,id'],
            'company_id' => ['required_without:guest_id', 'nullable', 'integer', 'exists:companies,id'],
            'transaction_id' => ['nullable', 'integer', 'exists:transactions,id'],
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['SAR', 'USD', 'EUR', 'GBP', 'AED'])],
            'exchange_rate' => ['required', 'numeric', 'min:0.0001'],
            'payment_method' => ['required', 'string', Rule::in(['cash', 'card', 'bank_transfer', 'cheque', 'online', 'other'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'cheque_number' => ['nullable', 'string', 'max:255'],
            'card_last_four' => ['nullable', 'string', 'size:4'],
            'description' => ['nullable', 'string', 'max:1000'],
            'receipt_date' => ['required', 'date'],
            'receipt_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(['draft', 'confirmed'])],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_id.required_without' => 'Either guest or company must be specified.',
            'company_id.required_without' => 'Either guest or company must be specified.',
            'amount.min' => 'Amount must be greater than 0.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'card_last_four.size' => 'Card last four must be exactly 4 digits.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Calculate amount_base if not provided
        if ($this->has('amount') && $this->has('exchange_rate')) {
            $amount = (float) $this->input('amount');
            $exchangeRate = (float) $this->input('exchange_rate');
            $this->merge([
                'amount_base' => round($amount * $exchangeRate, 2),
            ]);
        }
    }
}
