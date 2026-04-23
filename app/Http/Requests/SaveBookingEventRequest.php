<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveBookingEventRequest extends FormRequest {
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'title' => ['required', 'string', 'max:255'],
            'property_reference' => ['nullable', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'type' => ['required', 'in:viewing,inspection,public_event'],
            'description' => ['nullable', 'string'],
        ];
    }
}
