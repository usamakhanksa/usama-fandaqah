<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRoomRequest extends FormRequest {
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'room_number' => ['required', 'string', Rule::unique('rooms')->ignore($this->room)],
            'type' => ['required', 'string'],
            'floor' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:clean,dirty,inspecting,out_of_order'],
        ];
    }
}
