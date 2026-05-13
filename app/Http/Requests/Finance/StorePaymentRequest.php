<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        
        // Super Admin bypasses all permission checks
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        return $user->can('payments.create');
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['nullable', 'integer', 'exists:reservations,id'],
            'folio_id' => ['nullable', 'integer', 'exists:folios,id'],
            'guest_id' => ['required_without:company_id', 'nullable', 'integer', 'exists:guests,id'],
            'company_id' => ['required_without:guest_id', 'nullable', 'integer', 'exists:companies,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['SAR', 'USD', 'EUR', 'GBP', 'AED'])],
            'exchange_rate' => ['required', 'numeric', 'min:0.0001'],
            'payment_method' => ['required', 'string', Rule::in([
                'cash', 'visa', 'mastercard', 'mada', 'amex', 
                'bank_transfer', 'check', 'online', 'apple_pay', 'stc_pay', 'other'
            ])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'card_type' => ['required_if:payment_method,visa,mastercard,mada,amex', 'string', 'max:50'],
            'card_last_four' => ['required_if:payment_method,visa,mastercard,mada,amex', 'string', 'size:4'],
            'card_holder_name' => ['required_if:payment_method,visa,mastercard,mada,amex', 'string', 'max:255'],
            'check_number' => ['required_if:payment_method,check', 'string', 'max:255'],
            'check_date' => ['required_if:payment_method,check', 'date'],
            'bank_id' => ['nullable', 'integer', 'exists:banks,id'],
            'sender_id' => ['nullable', 'integer', 'exists:senders,id'],
            'payment_date' => ['required', 'date'],
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
            'guest_id.required_without' => 'Either guest or company must be specified.',
            'company_id.required_without' => 'Either guest or company must be specified.',
            'amount.min' => 'Amount must be greater than 0.',
            'exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'card_type.required_if' => 'Card type is required for card payments.',
            'card_last_four.required_if' => 'Card last four digits are required for card payments.',
            'card_last_four.size' => 'Card last four must be exactly 4 digits.',
            'card_holder_name.required_if' => 'Card holder name is required for card payments.',
            'check_number.required_if' => 'Check number is required for check payments.',
            'check_date.required_if' => 'Check date is required for check payments.',
            'commission_rate.max' => 'Commission rate cannot exceed 100%.',
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

        // Set default values for boolean fields
        $this->merge([
            'is_deposit' => $this->has('is_deposit') ? (bool) $this->input('is_deposit') : false,
            'is_advance' => $this->has('is_advance') ? (bool) $this->input('is_advance') : false,
        ]);
    }
}
