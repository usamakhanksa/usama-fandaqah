<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Super Admin bypasses all permission checks
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->can('payments.edit');
    }

    public function rules(): array
    {
        $payment = $this->route('payment');
        
        // Prevent updates if payment is not pending
        if ($payment && !$payment->isEditable()) {
            return [
                '_status_check' => ['required', 'in:pending'], // This will always fail
            ];
        }

        return [
            'reservation_id' => ['nullable', 'integer', 'exists:reservations,id'],
            'folio_id' => ['nullable', 'integer', 'exists:folios,id'],
            'guest_id' => ['nullable', 'integer', 'exists:guests,id'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'currency' => ['nullable', 'string', 'size:3', Rule::in(['SAR', 'USD', 'EUR', 'GBP', 'AED'])],
            'exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'payment_method' => ['nullable', 'string', Rule::in([
                'cash', 'visa', 'mastercard', 'mada', 'amex', 
                'bank_transfer', 'check', 'online', 'apple_pay', 'stc_pay', 'other'
            ])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'card_type' => ['nullable', 'string', 'max:50'],
            'card_last_four' => ['nullable', 'string', 'size:4'],
            'card_holder_name' => ['nullable', 'string', 'max:255'],
            'check_number' => ['nullable', 'string', 'max:255'],
            'check_date' => ['nullable', 'date'],
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'sender_id' => ['nullable', 'integer', 'exists:senders,id'],
            'payment_date' => ['nullable', 'date'],
            'payment_time' => ['nullable', 'date_format:H:i'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'cashier_id' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_deposit' => ['boolean'],
            'is_advance' => ['boolean'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'travel_agent_id' => ['nullable', 'integer', 'exists:travel_agents,id'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            '_status_check.in' => 'Only pending payments can be edited.',
            'amount.min' => 'Amount must be greater than 0.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'card_last_four.size' => 'Card last four must be exactly 4 digits.',
            'commission_rate.max' => 'Commission rate cannot exceed 100%.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Calculate amount_base if amount or exchange_rate changed
        if ($this->has('amount') || $this->has('exchange_rate')) {
            $amount = (float) ($this->input('amount') ?? $this->route('payment')?->amount ?? 0);
            $exchangeRate = (float) ($this->input('exchange_rate') ?? $this->route('payment')?->exchange_rate ?? 1);
            $this->merge([
                'amount_base' => round($amount * $exchangeRate, 2),
            ]);
        }

        // Convert boolean fields
        if ($this->has('is_deposit')) {
            $this->merge(['is_deposit' => (bool) $this->input('is_deposit')]);
        }
        
        if ($this->has('is_advance')) {
            $this->merge(['is_advance' => (bool) $this->input('is_advance')]);
        }
    }
}
