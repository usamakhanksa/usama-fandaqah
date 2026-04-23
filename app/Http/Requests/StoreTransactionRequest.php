<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'booking_reference' => 'nullable|string|max:50',
            'ar_account_id' => 'nullable|exists:ar_accounts,id',
            'type' => 'required|in:charge,payment,refund,adjustment',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'description' => 'required|string|max:255',
            'transaction_date' => 'required|date',
        ];
    }
}
