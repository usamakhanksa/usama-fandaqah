<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('transactions.create');
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['nullable', 'integer', 'exists:reservations,id'],
            'type' => ['required', 'in:payment,refund,deposit,withdrawal,charge,correction'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['required_if:type,payment', 'nullable', 'integer', 'exists:payment_methods,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'transaction_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required_if' => 'Payment method is required for payment transactions.',
            'amount.min' => 'Amount must be greater than 0.',
        ];
    }
}
