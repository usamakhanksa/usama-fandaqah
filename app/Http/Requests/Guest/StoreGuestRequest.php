<?php

namespace App\Http\Requests\Guest;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Guest::class);
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'relation_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:255',
            'id_type' => 'nullable|string|max:50',
            'customer_type' => 'nullable|integer',
            'country_id' => 'nullable|exists:countries,id',
            'shomoos_id' => 'nullable|string|max:255',
        ];
    }
}
