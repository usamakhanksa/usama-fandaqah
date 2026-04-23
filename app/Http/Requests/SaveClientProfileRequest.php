<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveClientProfileRequest extends FormRequest {
    public function authorize(): bool { return true; }

    public function rules(): array {
        $profileId = $this->route('client_relation') ? $this->route('client_relation')->id : null;
        
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('client_profiles')->ignore($profileId)],
            'phone' => ['required', 'string', 'max:50'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'type' => ['required', 'in:tenant,buyer,investor'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
        ];
    }
}
