<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'exists:room_types,id'],
            'room_floor_id' => ['nullable', 'exists:room_floors,id'],
            'number' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:120'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,occupied,reserved,maintenance,cleaning,not_ready'],
            'gender' => ['nullable', 'in:all,male,female'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
        ];
    }
}
