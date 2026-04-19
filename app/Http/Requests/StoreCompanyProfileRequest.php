<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'mobile_number' => ['nullable', 'string', 'max:40'],
            'responsible_person_name' => ['nullable', 'string', 'max:255'],
            'responsible_mobile_number' => ['nullable', 'string', 'max:40'],
            'id_type' => ['nullable', 'string', 'max:100'],
            'id_number' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['integer', 'exists:uploaded_media,id'],
        ];
    }
}
