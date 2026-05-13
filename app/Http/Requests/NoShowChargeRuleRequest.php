<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoShowChargeRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'charge_type' => 'required|in:fixed,percentage',
            'charge_amount' => 'required|numeric|min:0',
            'applies_to' => 'required|in:all,daily,monthly',
            'is_active' => 'boolean'
        ];
    }
}
