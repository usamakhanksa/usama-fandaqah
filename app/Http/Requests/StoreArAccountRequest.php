<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArAccountRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'company_name' => 'required|string|unique:ar_accounts,company_name,' . $this->route('ar_account'),
            'contact_person' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:50',
            'credit_limit' => 'required|numeric|min:0',
            'status' => 'required|in:active,suspended,closed',
        ];
    }
}
