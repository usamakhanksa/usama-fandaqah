<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckinRequest extends FormRequest {
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'room_number' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:arrival,in_house,departure,checked_out'],
            'id_verified' => ['boolean'],
            'deposit_collected' => ['numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
