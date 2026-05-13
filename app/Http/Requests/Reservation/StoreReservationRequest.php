<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reservations.create');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1', 'max:10'],
            'children' => ['nullable', 'integer', 'min:0', 'max:10'],
            'source_id' => ['nullable', 'integer', 'exists:sources,id'],
            'rate_code' => ['nullable', 'string', 'max:50'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'guests' => ['nullable', 'array'],
            'guests.*.name' => ['required_with:guests', 'string', 'max:255'],
            'guests.*.id_number' => ['nullable', 'string', 'max:50'],
            'guests.*.is_primary' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer is invalid.',
            'check_in.required' => 'Check-in date is required.',
            'check_in.after_or_equal' => 'Check-in date must be today or in the future.',
            'check_out.required' => 'Check-out date is required.',
            'check_out.after' => 'Check-out date must be after check-in date.',
            'adults.required' => 'Number of adults is required.',
            'adults.min' => 'At least 1 adult is required.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'adults' => $this->input('adults', 1),
            'children' => $this->input('children', 0),
        ]);
    }
}
