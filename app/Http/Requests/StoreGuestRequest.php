<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'company_profile_id' => ['nullable', 'exists:company_profiles,id'],
            'type' => ['required', 'in:vip,normal,company'],
            'gender' => ['required', 'in:male,female'],
            'card_id' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'drop_down_civn' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'read_only_field' => ['nullable', 'string', 'max:255'],
        ];
    }
}
